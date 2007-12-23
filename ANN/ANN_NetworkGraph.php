<?php

/**
 * Artificial Neural Network - Version 2.0.0
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
 * @version ANN Version 2.0.1 by Thomas Wien
 * @copyright Copyright (c) 2002 Eddy Young
 * @copyright Copyright (c) 2007 Thomas Wien
 * @package ANN
 */

/**
 * @package ANN
 * @access public
 */

class ANN_NetworkGraph
{
/**#@+
 * @ignore
 */

protected $numberInputs;
protected $numberHiddenLayers;
protected $numberNeuronsOfHiddenLayer;
protected $numberOfOutputs;
protected $image;
protected $colorNeuronInput;
protected $colorNeuronHidden;
protected $colorNeuronOutput;
protected $colorNeuronBorder;
protected $colorBackground;
protected $colorConnection;
protected $maxNeuronsPerLayer;
protected $layerDistance = 250;
protected $neuronDistance = 50;

/**#@-*/

// ****************************************************************************

/**
 * @param ANN_Network $network
 * @uses createImage()
 * @uses drawNetwork()
 * @uses ANN_Network::getNumberHiddenLayers()
 * @uses ANN_Network::getNumberInputs()
 * @uses ANN_Network::getNumberHiddens()
 * @uses ANN_Network::getNumberOutputs()
 */

public function __construct(ANN_Network $network)
{
$this->numberInputs = $network->getNumberInputs();
$this->numberHiddenLayers = $network->getNumberHiddenLayers();
$this->numberNeuronsOfHiddenLayer = $network->getNumberHiddens();
$this->numberOfOutputs = $network->getNumberOutputs();

$this->maxNeuronsPerLayer = max($this->numberInputs, $this->numberNeuronsOfHiddenLayer, $this->numberOfOutputs);

$this->createImage();

$this->drawNetwork();
}

// ****************************************************************************

/**
 * @uses drawConnections()
 * @uses drawHiddenNeurons()
 * @uses drawInputNeurons()
 * @uses drawOutputNeurons()
 */

protected function drawNetwork()
{
$this->drawConnections();

$this->drawInputNeurons();
$this->drawHiddenNeurons();
$this->drawOutputNeurons();
}

// ****************************************************************************

/**
 * @uses drawConnectionsHiddenOutput()
 * @uses drawConnectionsHiddens()
 * @uses drawConnectionsInputHidden()
 */

protected function drawConnections()
{
$this->drawConnectionsInputHidden();
$this->drawConnectionsHiddens();
$this->drawConnectionsHiddenOutput();
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawConnectionsInputHidden()
{
$yposHiddenStart = $this->calculateYPosStart($this->numberNeuronsOfHiddenLayer);
$yposInputStart = $this->calculateYPosStart($this->numberInputs);

for($input=0; $input < $this->numberInputs; $input++)
for($hidden=0; $hidden < $this->numberNeuronsOfHiddenLayer; $hidden++)
{
$xposInput = 100;
$yposInput = $yposInputStart + $this->neuronDistance * $input;

$xposHidden = 100 + $this->layerDistance;
$yposHidden = $yposHiddenStart + $this->neuronDistance * $hidden;

imageline($this->image, $xposInput, $yposInput, $xposHidden, $yposHidden, $this->colorConnection);
}
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawConnectionsHiddenOutput()
{
for($layer=0; $layer < $this->numberHiddenLayers; $layer++)
  $xposHidden = 100 + $this->layerDistance + $this->layerDistance * $layer;

$yposHiddenStart = $this->calculateYPosStart($this->numberNeuronsOfHiddenLayer);
$yposOutputStart = $this->calculateYPosStart($this->numberOfOutputs);

for($output=0; $output < $this->numberOfOutputs; $output++)
for($hidden=0; $hidden < $this->numberNeuronsOfHiddenLayer; $hidden++)
{
$xposHidden = $xposHidden;
$yposHidden = $yposHiddenStart + $this->neuronDistance * $hidden;

$xposOutput = $xposHidden + $this->layerDistance;
$yposOutput = $yposOutputStart + $this->neuronDistance * $output;

imageline($this->image, $xposHidden, $yposHidden, $xposOutput, $yposOutput, $this->colorConnection);
}
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawConnectionsHiddens()
{
if($this->numberHiddenLayers <= 1) return;

$yposHiddenStart = $this->calculateYPosStart($this->numberNeuronsOfHiddenLayer);

for($layer=1; $layer < $this->numberHiddenLayers; $layer++)
for($hidden1=0; $hidden1 < $this->numberNeuronsOfHiddenLayer; $hidden1++)
for($hidden2=0; $hidden2 < $this->numberNeuronsOfHiddenLayer; $hidden2++)
{
$xposHidden1 = 100 + $this->layerDistance + $this->layerDistance * $layer - $this->layerDistance;
$yposHidden1 = $yposHiddenStart + $this->neuronDistance * $hidden1;

$xposHidden2 = 100 + $this->layerDistance + $this->layerDistance * $layer;
$yposHidden2 = $yposHiddenStart + $this->neuronDistance * $hidden2;

imageline($this->image, $xposHidden1, $yposHidden1, $xposHidden2, $yposHidden2, $this->colorConnection);
}
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawInputNeurons()
{
$yposInputStart = $this->calculateYPosStart($this->numberInputs);

for($idx=0; $idx < $this->numberInputs; $idx++)
{
imagefilledellipse($this->image, 100, $yposInputStart + $this->neuronDistance * $idx, 30, 30, $this->colorNeuronInput);
imageellipse($this->image, 100, $yposInputStart + $this->neuronDistance * $idx, 30, 30, $this->colorNeuronBorder);
}
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawHiddenNeurons()
{
$yposHiddenStart = $this->calculateYPosStart($this->numberNeuronsOfHiddenLayer);

for($layer=0; $layer < $this->numberHiddenLayers; $layer++)
for($neuron=0; $neuron < $this->numberNeuronsOfHiddenLayer; $neuron++)
{
imagefilledellipse($this->image, 100 + $this->layerDistance + $this->layerDistance *$layer, $yposHiddenStart + $this->neuronDistance * $neuron, 30, 30, $this->colorNeuronHidden);
imageellipse($this->image, 100 + $this->layerDistance + $this->layerDistance * $layer, $yposHiddenStart + $this->neuronDistance * $neuron, 30, 30, $this->colorNeuronBorder);
}
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawOutputNeurons()
{
for($layer=0; $layer < $this->numberHiddenLayers; $layer++)
$xpos = 100 + $this->layerDistance + $this->layerDistance * $layer;

$yposStart = $this->calculateYPosStart($this->numberOfOutputs);

for($neuron=0; $neuron < $this->numberOfOutputs; $neuron++)
{
imagefilledellipse($this->image, $xpos + $this->layerDistance, $yposStart + $this->neuronDistance * $neuron, 30, 30, $this->colorNeuronOutput);
imageellipse($this->image, $xpos + $this->layerDistance, $yposStart + $this->neuronDistance * $neuron, 30, 30, $this->colorNeuronBorder);
}
}

// ****************************************************************************

/**
 * @uses calculateImageHeight()
 * @uses calculateImageWidth()
 * @uses setBackground()
 * @uses setColors()
 */

protected function createImage()
{
$this->image = imagecreatetruecolor($this->calculateImageWidth(), $this->calculateImageHeight());

$this->setColors();

$this->setBackground();
}

// ****************************************************************************

protected function setColors()
{
$this->colorBackground = imagecolorallocate($this->image, 200, 200, 200);

$this->colorNeuronInput = imagecolorallocate($this->image, 0, 255, 0);
$this->colorNeuronHidden = imagecolorallocate($this->image, 255, 0, 0);
$this->colorNeuronOutput = imagecolorallocate($this->image, 0, 0, 255);

$this->colorConnection = imagecolorallocate($this->image, 155, 255, 155);

$this->colorNeuronBorder = imagecolorallocate($this->image, 0, 0, 0);
}

// ****************************************************************************

protected function setBackground()
{
imagefill($this->image, 0, 0, $this->colorBackground);
}

// ****************************************************************************

/**
 * @return binary Image
 */

public function getImage()
{
ob_start();

imagepng($this->image);

$return = ob_get_contents();

ob_end_clean();

return $return;
}

// ****************************************************************************

/**
 * @uses getImage()
 */

public function printImage()
{
header('Content-type: image/png');

print $this->getImage();
}

// ****************************************************************************

/**
 * @param integer $numberNeurons
 * @return integer
 */

protected function calculateYPosStart($numberNeurons)
{
$v1 = $this->maxNeuronsPerLayer * $this->neuronDistance / 2;

$v2 = $numberNeurons * $this->neuronDistance / 2;

return $v1 - $v2 + $this->neuronDistance;
}

// ****************************************************************************

/**
 * @return integer Pixel
 */

protected function calculateImageHeight()
{
return (int)($this->maxNeuronsPerLayer * $this->neuronDistance + $this->neuronDistance);
}

// ****************************************************************************

/**
 * @return integer Pixel
 */

protected function calculateImageWidth()
{
return (int)(($this->numberHiddenLayers + 2) * $this->layerDistance);
}

// ****************************************************************************

/**
 * @param string $filename
 * @uses getImage()
 */

public function saveToFile($filename)
{
file_put_contents($filename, $this->getImage());
}

// ****************************************************************************
}

?>
