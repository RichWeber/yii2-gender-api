<?php

namespace richweber\gender\components;

use richweber\gender\helpers\SupportedCountries;
use yii\base\Component;
use yii\helpers\Json;
use yii\httpclient\Client;

/**
 * Gender API
 *
 * Keep your registration forms simple. Optimize your conversions
 * and let us determine the gender of your customers.
 *
 * @package richweber\gender\components
 */
class Gender extends Component
{
    const URL = 'https://gender-api.com';

    /**
     * @var string API key
     */
    public $serverKey;

    /**
     * @var array Request data
     */
    private $_data = [];

    /**
     * @var boolean Flag of stats
     */
    private $_isStatRequest = false;

    /**
     * @inheritdoc
     * @throws \richweber\gender\components\GenderException
     */
    public function init()
    {
        if ($this->serverKey === null) {
            throw new GenderException('Private server key is invalid');
        }

        parent::init();
    }

    /**
     * This is the easiest way to request a gender determination.
     * Sometimes it is necessary to fetch multiple names with one query.
     *
     * @param string|array $data Name|Names
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws GenderException
     */
    public function checkName($data)
    {
        if (is_array($data)) {
            if (count($data) > 100) {
                throw new GenderException('The maximum number of names is limited to 100');
            }

            $this->_data['name'] = implode(';', $data);
        } else {
            $this->_data['name'] = $data;
        }

        return $this->getResponse();
    }

    /**
     * You can also query a gender by an email address which contains a first name
     *
     * ```
     * {"email":"markus.p@gmail.com","name":"markus","gender":"male","samples":150,"accuracy":99,"duration":"44ms"}
     * {"email":"jack@gmail.com","name":"jack","country":"US","gender":"male","samples":6,"accuracy":67,"duration":"49ms"}
     * ```
     *
     * @param string $email Email address
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws \richweber\gender\components\GenderException
     */
    public function checkNameByEmail($email)
    {
        $this->_data['email'] = $email;
        return $this->getResponse();
    }

    /**
     * If you have a combined field for the first and the last name on your page use this api to extract the parts.
     * Please hint your customers to enter the name in the form "First Name, Last Name" which makes our response more accurate.
     *
     * ```
     * {"last_name":"Miller","first_name":"Theresa","name":"theresa","gender":"female","samples":8065,"accuracy":98,"duration":"56ms"}
     * {"last_name":"Johnson","first_name":"Tim","name":"tim","country":"US","gender":"male","samples":9509,"accuracy":100,"duration":"46ms"}
     * ```
     *
     * @param string $names First & Last Name
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws \richweber\gender\components\GenderException
     */
    public function checkSplitNames($names)
    {
        $this->_data['split'] = urlencode($names);
        return $this->getResponse();
    }

    /**
     * Add more accuracy to request by localizing query
     *
     * ```
     * {"name":"andrea","gender":"male","country":"IT","samples":160,"accuracy":97,"duration":"29ms"} //In Italy, Andrea is male.
     * {"name":"andrea","gender":"female","country":"DE","samples":19,"accuracy":95,"duration":"31ms"} //In Germany, Andrea is female.
     * ```
     *
     * @param string $country Country
     *
     * @return $this
     * @throws GenderException
     */
    public function byLocalization($country)
    {
        if (!SupportedCountries::isSupportedCountry($country)) {
            throw new GenderException('Not supported country');
        }

        $this->_data['country'] = $country;
        return $this;
    }

    /**
     * Choose the country of customer based on his IP address
     *
     * ```
     * {"name":"John","country":"US","gender":"male","samples":4,"accuracy":100,"duration":"38ms"} //Country: US
     * {"name":"Tanja","country":"DE","gender":"female","samples":10,"accuracy":100,"duration":"36ms"} //Country: Germany.
     * {"name":"Thomas","country":"DE","gender":"male","samples":13,"accuracy":100,"duration":"39ms"} //Country: Based on the browser IP. Can only be used by the Javascript API.
     * ```
     *
     * @param string $ip IP
     *
     * @return $this
     */
    public function byIP($ip)
    {
        $this->_data['ip'] = $ip;
        return $this;
    }

    /**
     * Choose the country of customer based on his browser locale
     *
     * ```
     * {"name":"John","country":"US","gender":"male","samples":4,"accuracy":100,"duration":"38ms"} //Country: US
     * {"name":"Tanja","country":"DE","gender":"female","samples":10,"accuracy":100,"duration":"36ms"} //Country: Germany.
     * {"name":"Thomas","country":"DE","gender":"male","samples":13,"accuracy":100,"duration":"39ms"} //Country: Based on the browser language. Can only be used by the Javascript API.
     * ```
     *
     * @param string $language Language
     *
     * @return $this
     */
    public function byLanguage($language)
    {
        $this->_data['language'] = $language;
        return $this;
    }

    /**
     * Get stats by account
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws \richweber\gender\components\GenderException
     */
    public function getStats()
    {
        $this->_isStatRequest = true;
        return $this->getResponse();
    }

    /**
     * Get response
     *
     * @return mixed
     * @throws \yii\base\InvalidParamException
     * @throws GenderException
     */
    public function getResponse()
    {
        $client = new Client(['baseUrl' => self::URL]);
        $response = $client->createRequest()
            ->setMethod('get')
            ->setUrl($this->_isStatRequest ? 'get-stats' : 'get')
            ->setData(array_merge($this->_data, ['key' => $this->serverKey]))
            ->send();

        if ($response->isOk) {
            return Json::decode($response->content, false);
        } else {
            throw new GenderException('Gender API response error');
        }
    }
}
