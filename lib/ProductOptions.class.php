<?php

class ProductOptions extends Factory 
{
	protected $singularClassName = 'ProductOption';
    protected $table    = 'tblProductOptions';
    protected $pk   = 'optionID';

    protected $default_sort_column  = 'optionDesc';  




}