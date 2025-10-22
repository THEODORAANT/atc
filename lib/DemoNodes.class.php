<?php

class DemoNodes extends Factory 
{
	protected $singularClassName = 'DemoNode';
    protected $table    = 'tblDemoNodes';
    protected $pk   = 'nodeID';

    protected $default_sort_column  = 'nodeLastSeen';  
    protected $created_date_column  = 'nodeLastSeen';
    protected $modified_date_column = 'nodeLastSeen';
}