<?php

namespace App\Contracts\Notification;

interface Notification
{
    /**
     * Return Notification Message
     *
     * @param array $data
     * @return string
     */
    public static function getMessage(array $data): string;


    /**
     * Return Notification Icon URL
     *
     * @param array $data
     * @return string
     */
    public static function getIconURL(array $data = []): string;

    /**
     * Return Notification Route Name
     *
     * @param array $data
     * @return string
     */
    public static function getRouteName(array $data = []): string;
}
