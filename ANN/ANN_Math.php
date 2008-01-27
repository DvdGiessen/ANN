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
 * @copyright Copyright (c) 2007-08 by Thomas Wien
 * @package ANN
 */


/**
 * @package ANN
 * @access private
 */

class ANN_Maths
{
// ****************************************************************************

/**
 * @param float $x
 * @return float (between near 0 and near 1)
 */

public static function sigmoid($x)
{
  return 1 / (1 + exp(-1 * $x));
}

// ****************************************************************************

/**
 * First derivative of sigmoid()
 *
 * @param float $x
 * @return float (between near 0 and near 1)
 */

public static function sigmoidI($x)
{
  return self::sigmoid($x) * (1 - self::sigmoid($x));
}

// ****************************************************************************

/**
 * @param float $x
 * @return float (between near 0 and near 1)
 */

public static function tangensHyperbolicus($x)
{
  return tanh($x);
}

// ****************************************************************************

/**
 * @param float $x
 * @return float (between near 0 and near 1)
 */

public static function tangensHyperbolicus01($x)
{
  return (tanh($x) + 1) / 2;
}

// ****************************************************************************

/**
 * First derivative of tanh()
 *
 * @param float $x
 * @return float (between near 0 and near 1)
 */

public static function tangensHyperbolicusI($x)
{
  return 1 - pow(tanh($x), 2);
}

// ****************************************************************************

/**
 * @param float $x
 * @return integer (0 or 1)
 */

public static function threshold($x)
{
  return ($x > 0.5) ? 1 : 0;
}

// ****************************************************************************

/**
 * @param integer $min (Default:  0)
 * @param integer $max (Default:  10)
 * @return integer
 */

public static function random($min = 0, $max = 10)
{
  return mt_rand($min, $max);
}

// ****************************************************************************

/**
 * Return the sign of a number
 *
 * If $value is positiv the method returns 1 otherwise -1.
 *
 * @param float $value
 * @return integer
 */

public static function sign($value)
{
  if($value >= 0) return 1;

  return -1;
}

// ****************************************************************************

/**
 * @param float $x
 * @return float (-1 .. 1)
 */

public static function linearSaturated($x)
{
  if($x < -1) return -1;
  if($x > 1) return 1;

  return $x;
}

// ****************************************************************************

/**
 * @param float $x
 * @return float (0 .. 1)
 * @uses self::saturated()
 */

public static function linearSaturated01($x)
{
  if($x < -1) $x = -1;
  if($x > 1)  $x = 1;

  return ($x + 1) / 2;
}

// ****************************************************************************
}

?>
