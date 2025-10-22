<?php

class OrderItems extends Factory 
{
	protected $singularClassName = 'OrderItem';
    protected $table    = 'tblOrderItems';
    protected $pk   = 'itemID';

    protected $default_sort_column  = 'itemTotalIncVat';  


    /**
     * Get orderitems for the given orderID
     * @param  [type] $orderID [description]
     * @return [type]          [description]
     */
    public function get_for_order($orderID)
    {
        return $this->get_by('orderID', $orderID);
    }

    public function get_description($orderID)
    {
        $desc = [];
        $items = $this->get_for_order($orderID);

        if (Util::count($items)) {
            foreach($items as $Item) {
                $desc[] = $Item->itemDescription();
            }
        }

        return implode(', ', $desc);
    }

    public function get_items_xml($orderID)
    {
        $items = $this->get_for_order($orderID);

        $item_xml = [];

        if (Util::count($items)) {
            foreach($items as $Item) {
                $item_xml[] = [
                    'description'      => $Item->itemDescription(),
                    'productCode'      => $Item->itemCode(),
                    'quantity'         => $Item->itemQty(),
                    'unitNetAmount'    => number_format($Item->itemUnitPrice(), 2),
                    'unitTaxAmount'    => number_format($Item->itemUnitVat(), 2),
                    'unitGrossAmount'  => number_format(($Item->itemUnitPrice() + $Item->itemUnitVat()), 2),
                    'totalGrossAmount' => number_format($Item->itemTotalIncVat(), 2),
                ];
            }

            $xml = new SimpleXMLElement('<basket/>');
            foreach($item_xml as $item) {
                $this_item = $xml->addChild('item');
                foreach($item as $key=>$val) {
                    $this_item->addChild($key, $val);
                }
            }
            return $xml->asXML();
        }

        return false;
    }

}