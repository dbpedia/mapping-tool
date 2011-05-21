<?php

if(!defined('__INCLUDE_LOADED__')){
    die('please include "include.php" for functionality');
}

$q = Doctrine_Query::create()
    ->select('d.*')
    ->from('Datatype d')
    ->orderBy('d.label');

$datatypes = $q->execute();

$datatypes = $datatypes->toArray(true);

$dimensions = array(
    "Area", "Currency", "Density", "Energy", "FlowRate", "Force", 
    "FuelEfficiency", "Frequency", "InformationUnit", "Length", 
    "LinearMassDensity", "Mass", "PopulationDensity", "Power", "Pressure", 
    "Speed", "Temperature", "Time", "Torque", "Volume"
);

$tmp = array();
foreach($datatypes as $datatype){

  $datatype['name'] = in_array(ucfirst($datatype['name']), $dimensions ) ? ucfirst($datatype['name']) : $datatype['name'];
  
  $tmp[] = array(
    'text'     => $datatype['label'],
    'label'    => $datatype['name'],
    'value'    => $datatype['name'],
    'name'     => $datatype['name'],
    'type'     => 'Datatype',
    //'nodeType' => PREFIX . 'Datatype',
    'iconCls'  => 'my-tree-icon-DBpediaDatatype',
    'leaf'     => true
  );
}

Tht_Helper_Header::JS();
echo json_encode($tmp);
