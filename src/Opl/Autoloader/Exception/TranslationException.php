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
namespace Opl\Autoloader\Exception;
use RuntimeException;

/**
 * The exception allows to report class-to-filename translation problems.
 * It is used in the toolset. The autoloaders are not allowed to throw
 * sophisticated exceptions, as they are not a part of the application, but
 * the runtime environment.
 * 
 * @author Tomasz Jędrzejewski
 * @copyright Invenzzia Group <http://www.invenzzia.org/> and contributors.
 * @license http://www.invenzzia.org/license/new-bsd New BSD License
 */
class TranslationException extends RuntimeException
{
	
} // end TranslationException;