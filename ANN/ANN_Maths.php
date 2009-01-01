<?php

/**
 * Artificial Neural Network - Version 2.0
 *
 * For updates and changes visit the project page at http://ann.thwien.de/
 *
 *
 *
 * <b>LICENCE</b>
 *
 * This source file is freely re-distributable, with or without modifications
 * provided the following conditions are met:
 * 
 * 1.	The source files must retain the copyright notice below, this list of
 *		conditions and the following disclaimer.
 *
 * 2.	The name of the author must not be used to endorse or promote products
 *		derived from this source file without prior written permission. For 
 *		written permission, please contact me.
 *
 * <b>DISCLAIMER</b>
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR `AS IS'' AND
 * ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A
 * PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE PHP
 * AUTHOR OR HIS CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 * INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
 * HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
 * STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
 * OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Eddy Young <jeyoung_at_priscimon_dot_com>
 * @author Thomas Wien <info_at_thwien_dot_de>
 * @version ANN Version 1.0 by Eddy Young
 * @version ANN Version 2.0 by Thomas Wien
 * @copyright Copyright (c) 2002 by Eddy Young
 * @copyright Copyright (c) 2007-09 by Thomas Wien
 * @package ANN
 */


/**
 * @package ANN
 * @access private
 */

class ANN_Maths
{
const PRECISION = 15;

// ****************************************************************************

/**
 * @param float $floatValue
 * @return float (between near 0 and near 1)
 */

public static function sigmoid($floatValue)
{
  return round(1 / (1 + exp(-1 * $floatValue)), self::PRECISION);
}

// ****************************************************************************

/**
 * First derivative of sigmoid()
 *
 * @param float $floatValue
 * @return float (between near 0 and near 1)
 */

public static function sigmoidI($floatValue)
{
  return round(self::sigmoid($floatValue) * (1 - self::sigmoid($floatValue)), self::PRECISION);
}

// ****************************************************************************

/**
 * @param float $floatValue
 * @return float (between near -1 and near 1)
 */

public static function tangensHyperbolicus($floatValue)
{
  return round(tanh($floatValue), self::PRECISION);
}

// ****************************************************************************

/**
 * @param float $floatValue
 * @return float (between near 0 and near 1)
 */

public static function tangensHyperbolicus01($floatValue)
{
  return round((tanh($floatValue) + 1) / 2, self::PRECISION);
}

// ****************************************************************************

/**
 * First derivative of tanh()
 *
 * @param float $floatValue
 * @return float (between near 0 and near 1)
 */

public static function tangensHyperbolicusI($floatValue)
{
  return round(1 - pow(tanh($floatValue), 2), self::PRECISION);
}

// ****************************************************************************

/**
 * @param float $floatValue
 * @return integer (0 or 1)
 */

public static function threshold($floatValue)
{
  return ($floatValue > 0.5) ? 1 : 0;
}

// ****************************************************************************

/**
 * @param integer $floatValueMin (Default:  0)
 * @param integer $floatValueMax (Default:  10)
 * @return integer
 */

public static function random($floatValueMin = 0, $floatValueMax = 10)
{
  return round(mt_rand($floatValueMin, $floatValueMax), self::PRECISION);
}

// ****************************************************************************

/**
 * Return the sign of a number
 *
 * If $floatValue is positive the method returns 1 otherwise -1.
 *
 * @param float $floatValue
 * @return integer
 */

public static function sign($floatValue)
{
  if($floatValue >= 0)
    return 1;

  return -1;
}

// ****************************************************************************

/**
 * @param float $floatValue
 * @return float (-1 .. 1)
 */

public static function linearSaturated($floatValue)
{
  if($floatValue < -1)
    return -1;
    
  if($floatValue > 1)
    return 1;

  return $floatValue;
}

// ****************************************************************************

/**
 * @param float $floatValue
 * @return float (0 .. 1)
 */

public static function linearSaturated01($floatValue)
{
  if($floatValue < -1)
    $floatValue = -1;
    
  if($floatValue > 1)
    $floatValue = 1;

  return ($floatValue + 1) / 2;
}

// ****************************************************************************
}

?>