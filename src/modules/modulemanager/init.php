<?php

/*
	Open Apexx Module Manager
	Copyright (C) 2020 Carsten Grings

	This program is free software: you can redistribute it and/or modify
	it under the terms of the GNU Lesser General Public License as published by
	the Free Software Foundation, either version 2.1 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU Lesser General Public License for more details.

	You should have received a copy of the GNU Lesser General Public License
	along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/


//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


//Modul registrieren
$module = array(1,99980,
	'id' => 'modulemanager',
	'dependence' => array(),
	'requirement' => array('main' => '1.2.0'),
	'version' => '1.0.0',
	'author' => 'Carsten Grings',
	'contact' => 'http://grings-software.de',
	'mediainput' => array()
);

//Navigation              S V O R
$action['show']  =  array(0,1,1,0);
$action['install']  =  array(0,1,1,0);
$action['repos']  =  array(0,1,1,0);
$action['deleteModule']  =  array(0,0,1,0);