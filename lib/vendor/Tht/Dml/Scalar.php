<?php

class Tht_Dml_Scalar implements Tht_Dml_IExtJsJsonTree
{
    protected $name = 'Scalar';
    protected $type = 'Scalar';
    public    $data = null;

    /**
     * create a Tht_Dml_Scalar object
     *
     * @param string $data
     * @return Tht_Dml_Scalar
     */
    public function __construct($data)
    {
        $this->data = $data;
        return $this;
    }

    public function getAsExtJsJsonTree()
    {
        return array(
            'text'     => $this->name,
            'nodeType' => PREFIX . $this->type,
            'value'    => $this->data,
            'leaf'     => true,
            'isTemplate' => false
        );
    }
}