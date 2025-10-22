<?php

class BankTransfers extends Factory 
{
	protected $singularClassName   = 'BankTransfer';
	protected $table               = 'tblBankTransfers';
	protected $pk                  = 'transferID';
	
	protected $default_sort_column = 'transferDateTime';  


}