<?php

class StripePaymentIntents extends Factory 
{
	protected $singularClassName = 'StripePaymentIntent';
    protected $table    = 'tblStripePaymentIntents';
    protected $pk   = 'id';
}