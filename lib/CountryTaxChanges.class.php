<?php

class CountryTaxChanges extends Factory 
{
	protected $singularClassName = 'CountryTaxChange';
    protected $table    = 'tblCountryTaxChanges';
    protected $pk   = 'changeID';

    protected $default_sort_column  = 'changeDate, countryID';

}

  