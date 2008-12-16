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
 * @author Thomas Wien <info_at_thwien_dot_de>
 * @version ANN Version 2.0 by Thomas Wien
 * @copyright Copyright (c) 2007-08 by Thomas Wien
 * @package ANN
 */


/**
 * @package ANN
 * @access public
 */

final class ANN_OutputValue extends ANN_Filesystem
{
/**#@+
 * @ignore
 */

protected $min;
protected $max;
protected $override = FALSE;

/**#@-*/

// ****************************************************************************

/**
 * @param float $min
 * @param float $max
 * @param boolean $override (Default: FALSE)
 * @throws ANN_Exception
 *
 * If $override is FALSE, an exception will be thrown if getOutputValue() will
 * be called with outranged values. If $override is TRUE, no exception will be
 * thrown in this case, but lower values are replaced by $min and upper values
 * are replaced by $max.
 */

public function __construct($min, $max, $override = FALSE)
{
  if(!is_float($min) && !is_integer($min))
    throw new ANN_Exception('Constraints: $min must be a float number');

  if(!is_float($max) && !is_integer($max))
    throw new ANN_Exception('Constraints: $min must be a float number');

  if($min > $max)
    throw new ANN_Exception('Constraints: $min should be lower than $max');

  if(!is_bool($override))
    throw new ANN_Exception('Constraints: $override must be boolean');

  $this->min = $min;
  
  $this->max = $max;
  
  $this->override = $override;
}

// ****************************************************************************

/**
 * @param float $value
 * @return float (0..1)
 * @uses calculateOutputValue()
 * @throws ANN_Exception
 */

public function getOutputValue($value)
{
  if(!$this->override && $value < $this->min)
    throw new ANN_Exception('Constraints: $value should be between '. $this->min .' and '. $this->max);

  if(!$this->override && $value > $this->max)
    throw new ANN_Exception('Constraints: $value should be between '. $this->min .' and '. $this->max);

  if($this->override && $value < $this->min)
    $value = $this->min;

  if($this->override && $value > $this->max)
    $value = $this->max;

  if($value >= $this->min && $value <= $this->max)
    return $this->calculateOutputValue($value);
}

// ****************************************************************************

/**
 * @param float $value (0..1)
 * @return float
 * @throws ANN_Exception
 */

public function getRealOutputValue($value)
{
  if($value < 0 || $value > 1)
    throw new ANN_Exception('Constraints: $value should be between 0 and 1');

  return $value * ($this->max - $this->min) + $this->min;
}

// ****************************************************************************

/**
 * @param float $value
 * @return float
 */

protected function calculateOutputValue($value)
{
  return ($value - $this->min) / ($this->max - $this->min);
}

// ****************************************************************************
}

?>
