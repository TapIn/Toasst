<?php

namespace FSStack\Gruppe\Models\User;

use \FSStack\Gruppe\Models;

/**
 * Tracks a language spoken by the user. Currently unused.
 */
class Language
{
    public static $table_name = 'users_languages';
    public static $primary_key = array('languageID', 'userID');

    /**
     * ID of the language the user speaks
     * @var number
     */
    protected $languageID;
    /**
     * ID of the user who speaks the language.
     * @var number
     */
    protected $userID;

    /**
     * Language the user speaks. Magic getter for $userLanguage->language
     * @return Models\Language Language the user speaks
     */
    public function __get_language()
    {
        return new Models\Language($this->languageID);
    }

    /**
     * User who speaks the language. Magic getter for $userLanguage->user
     * @return Models\User User who speaks the language
     */
    public function __get_user()
    {
        return new Models\User($this->userID);
    }
}
