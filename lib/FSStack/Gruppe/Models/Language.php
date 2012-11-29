<?php

namespace FSStack\Gruppe\Models;

class Language
{
    public static $table_name = 'languages';
    public static $primary_key = 'languageID';

    protected $languageID;
    protected $name;
    protected $translated;
}
