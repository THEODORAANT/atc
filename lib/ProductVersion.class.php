<?php

class ProductVersion extends Core_Base 
{
	protected $table  = 'tblProductVersions';
    protected $pk     = 'versionID';

    public function set_as_current_download()
    {
    	$sql = 'UPDATE :table
    			SET versionFileName=:file_name
    			WHERE productID=:productID
    				AND (versionMajor=3 OR versionMajor=2)';

    	$Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('file_name', $this->versionFileName());
        $Query->set('productID', $this->productID(), 'int');
        $Query->set('versionMajor', $this->versionMajor(), 'int');
 	
    	$this->db->execute($Query);


    	$sql = 'UPDATE :table
    			SET versionOnSale=0
    			WHERE productID=:productID
    				AND versionMajor=:versionMajor
    				AND versionID != :this_version';

    	$Query = Factory::get('Query', $sql);
        $Query->set('table', $this->table, 'table');
        $Query->set('this_version', $this->versionID(), 'int');
        $Query->set('productID', $this->productID(), 'int');
        $Query->set('versionMajor', $this->versionMajor(), 'int');
 	
    	$this->db->execute($Query);
    }

    public function log_download($versionID="",$downloadReferrer="perchrunway.com")
    {
        if(!$versionID){
            $versionID= $this->versionID();
        }
        $data = [
            'downloadDateTime' => date('Y-m-d H:i:s'),
            'productID' => $this->productID(),
            'versionID' =>  $versionID,
            'downloadReferrer'=>$downloadReferrer
        ];

        $this->db->insert('tblDownloads', $data);
    }

}
