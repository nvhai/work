<?php

namespace IWD\BluePay\Helper;

/**
 * Serialize data to JSON, unserialize JSON encoded data
 */
class Json
{
    const JSON_ERROR_NONE = 0;
    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function serialize($data)
    {
        $result = json_encode($data);
        if (false === $result) {
            throw new \InvalidArgumentException('Unable to serialize value.');
       }
        return $result;
    }
    /**
     * {@inheritDoc}
     * @since 100.2.0
     */
    public function unserialize($string)
    {
        $result = json_decode($string, true);
        if (json_last_error() !== self::JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Unable to unserialize value.');
       }
        return $result;
    }
}