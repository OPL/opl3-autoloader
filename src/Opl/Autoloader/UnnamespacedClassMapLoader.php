<?php
/*
 *  OPEN POWER LIBS <http://www.invenzzia.org>
 *
 * This file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE. It is also available through
 * WWW at this URL: <http://www.invenzzia.org/license/new-bsd>
 *
 * Copyright (c) Invenzzia Group <http://www.invenzzia.org>
 * and other contributors. See website for details.
 */
namespace Opl\Autoloader;
use DomainException;
use RuntimeException;

/**
 * This autoloader is based on the pre-computed class location
 * map. The map can be stored in a file and optionally cached
 * in the memory.
 * 
 * While the ClassMapLoader supports namespaces, this autoloader purposefully does not. This is to alleviate 
 * legacy issues with unnamespaced classes which happened when extending classes. 
 *
 * @author Tomasz Jędrzejewski
 * @author Baldur Rensch <baldur.rensch@hautelook.com>
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class UnnamespacedClassMapLoader extends ClassMapLoader
{
    /**
     * Attempts to load the specified class from a file.
     *
     * @param string $className The class name.
     * @return boolean
     */
    public function loadClass($className)
    {
        if(!isset($this->classMap[$className]))
        {
            return false;
        }
        require_once($this->defaultPath. $this->classMap[$className]);
    
        return true;
    } // end loadClass();
}