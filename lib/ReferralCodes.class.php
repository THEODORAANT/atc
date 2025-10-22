<?php

class ReferralCodes extends Factory 
{
	protected $singularClassName = 'ReferralCode';
    protected $table    = 'tblReferralCodes';
    protected $pk   = 'codeID';

    protected $default_sort_column  = 'codeRef';  



}