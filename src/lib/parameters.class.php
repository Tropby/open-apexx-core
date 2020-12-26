<?php

/*
	Open Apexx Core
	(c) Copyright 2020 Carsten Grings

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

class Parameters
{
	private bool $showErrors = false;
	private bool $security = false;
	
	public function __construct( bool $security = true, bool $showErrors = true, $unsetGlobalVariables = false )
	{		
		$this->showErrors = $showErrors;
		$this->security = $security;
	
		if( $unsetGlobalVariables )
		{
			unset($_GET);
			unset($_POST);
			unset($_REQUEST);
			unset($_COOKIE);
			unset($_SERVER);
		}		
	}
	
///////////////////////////////////////////////////////////////////////////////////////

	public function getIf(string $variable_name) : bool
	{
		return $this->_if($variable_name, INPUT_GET);
	}
	
	public function getInt(string $variable_name) 
	{
		return $this->_int($variable_name, INPUT_GET);
	}
	
	public function getString(string $variable_name) 
	{
		return $this->_string($variable_name, INPUT_GET);		
	}

///////////////////////////////////////////////////////////////////////////////////////

	public function postIf(string $variable_name) : bool
	{
		return $this->_if($variable_name, INPUT_POST);
	}
	
	public function postInt(string $variable_name) 
	{
		return $this->_int($variable_name, INPUT_POST);
	}
	
	public function postString(string $variable_name) 
	{
		return $this->_string($variable_name, INPUT_POST);
	}
	
///////////////////////////////////////////////////////////////////////////////////////

	public function requestIf(string $variable_name) : bool
	{
		return $this->postIf($variable_name) || $this->getIf($variable_name);
	}

	public function requestInt(string $variable_name) 
	{
		// Disable Errors for the first call
		$se = $this->showErrors;
		$this->showErrors = false;
		
		// try get Parameter from POST variable
		$result = $this->postInt($variable_name);

		// Reset show Errors 
		$this->showErrors = $se;
		
		if( $result === NULL )
		{
			$result = $this->getInt($variable_name);
		}
		
		return $result;
	}
	
	public function requestString(string $variable_name) 
	{
		// Disable Errors for the first call
		$se = $this->showErrors;
		$this->showErrors = false;
		
		// try get Parameter from POST variable
		$result = $this->postString($variable_name);

		// Reset show Errors 
		$this->showErrors = $se;
		
		if( $result === NULL )
		{
			$result = $this->getString($variable_name);
		}
		
		return $result;
	}	
	
///////////////////////////////////////////////////////////////////////////////////////
	
	private function _if(string $variable_name, int $type = INPUT_GET) : bool
	{
		return filter_has_var($type, $variable_name);
	}
	
	private function _int(string $variable_name, int $type = INPUT_GET) 
	{
		$result = filter_input($type, $variable_name, FILTER_VALIDATE_INT);
		if( ($result === false || $result === NULL) && $this->showErrors )
		{
			$result = NULL;
			ApexxError::ERROR( "Can not get input variable \"".$variable_name."\" as Integer!", $this->security );
		}
		return $result;					
	}
	
	private function _string(string $variable_name, int $type = INPUT_GET) 
	{	
		$result = filter_input($type, $variable_name, FILTER_DEFAULT);

		if( !is_string($result) && $this->showErrors )
		{
			$result = NULL;
			ApexxError::ERROR( "Can not get input variable \"".$variable_name."\" as String!", $this->security );
		}
		return $result;
	}	
}