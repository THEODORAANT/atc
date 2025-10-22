<?php

class Currencies extends Factory 
{
	protected $singularClassName = 'Currency';
    protected $table    = 'tblCurrencies';
    protected $pk   = 'currencyCode';

    protected $default_sort_column  = 'currencyCode';  

}