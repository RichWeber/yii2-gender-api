<?php

namespace richweber\gender\components;

use yii\base\Exception;

/**
 * Class GenderException
 * @package richweber\gender\components
 */
class GenderException extends Exception
{
    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'GenderException';
    }
}
