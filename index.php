<?php

$indexFilePath = './main';
$maintenanceFilePath = './maintenance';

if (file_exists($indexFilePath)) {    
    header('Location: ' . $indexFilePath);
} 
else {
    header('Location: ' . $maintenanceFilePath);
}