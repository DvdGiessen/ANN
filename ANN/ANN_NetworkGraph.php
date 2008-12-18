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

class ANN_NetworkGraph
{
/**#@+
 * @ignore
 */

protected $intNumberInputs;
protected $intNumberHiddenLayers;
protected $intNumberNeuronsOfHiddenLayer;
protected $intNumberOfOutputs;
protected $handleImage;
protected $handleColorNeuronInput;
protected $handleColorNeuronHidden;
protected $handleColorNeuronOutput;
protected $handleColorNeuronBorder;
protected $handleColorBackground;
protected $handleColorConnection;
protected $intMaxNeuronsPerLayer;
protected $intLayerDistance = 250;
protected $intNeuronDistance = 50;

/**#@-*/

// ****************************************************************************

/**
 * @param ANN_Network $objNetwork
 * @uses createImage()
 * @uses drawNetwork()
 * @uses ANN_Network::getNumberHiddenLayers()
 * @uses ANN_Network::getintNumberInputs()
 * @uses ANN_Network::getNumberHiddens()
 * @uses ANN_Network::getNumberOutputs()
 */

public function __construct(ANN_Network $objNetwork)
{
  $this->intNumberInputs = $objNetwork->getNumberInputs();

  $this->intNumberHiddenLayers = $objNetwork->getNumberHiddenLayers();

  $this->intNumberNeuronsOfHiddenLayer = $objNetwork->getNumberHiddens();

  $this->intNumberOfOutputs = $objNetwork->getNumberOutputs();

  $this->intMaxNeuronsPerLayer = max($this->intNumberInputs, $this->intNumberNeuronsOfHiddenLayer, $this->intNumberOfOutputs);

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
  $intYPosHiddenStart = $this->calculateYPosStart($this->intNumberNeuronsOfHiddenLayer);

  $intYPosInputStart = $this->calculateYPosStart($this->intNumberInputs);

  for($intIndexInput = 0; $intIndexInput < $this->intNumberInputs; $intIndexInput++)
    for($intIndexHidden = 0; $intIndexHidden < $this->intNumberNeuronsOfHiddenLayer; $intIndexHidden++)
    {
      $intXPosInput = 100;
      
      $intYPosInput = $intYPosInputStart + $this->intNeuronDistance * $intIndexInput;

      $intXPosHidden = 100 + $this->intLayerDistance;
      
      $intYPosHidden = $intYPosHiddenStart + $this->intNeuronDistance * $intIndexHidden;

      imageline($this->handleImage, $intXPosInput, $intYPosInput, $intXPosHidden, $intYPosHidden, $this->handleColorConnection);
    }
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawConnectionsHiddenOutput()
{
  for($intIndexLayer = 0; $intIndexLayer < $this->intNumberHiddenLayers; $intIndexLayer++)
    $intXPosHidden = 100 + $this->intLayerDistance + $this->intLayerDistance * $intIndexLayer;

  $intYPosHiddenStart = $this->calculateYPosStart($this->intNumberNeuronsOfHiddenLayer);

  $intYPosOutputStart = $this->calculateYPosStart($this->intNumberOfOutputs);

  for($intIndexOutput = 0; $intIndexOutput < $this->intNumberOfOutputs; $intIndexOutput++)
    for($intIndexHidden = 0; $intIndexHidden < $this->intNumberNeuronsOfHiddenLayer; $intIndexHidden++)
    {
      $intXPosHidden = $intXPosHidden;

      $intYPosHidden = $intYPosHiddenStart + $this->intNeuronDistance * $intIndexHidden;

      $intXPosOutput = $intXPosHidden + $this->intLayerDistance;

      $intYPosOutput = $intYPosOutputStart + $this->intNeuronDistance * $intIndexOutput;

      imageline($this->handleImage, $intXPosHidden, $intYPosHidden, $intXPosOutput, $intYPosOutput, $this->handleColorConnection);
  }
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawConnectionsHiddens()
{
  if($this->intNumberHiddenLayers <= 1)
    return;

  $intYPosHiddenStart = $this->calculateYPosStart($this->intNumberNeuronsOfHiddenLayer);

  for($intIndexLayer = 1; $intIndexLayer < $this->intNumberHiddenLayers; $intIndexLayer++)
    for($intIndexHidden1 = 0; $intIndexHidden1 < $this->intNumberNeuronsOfHiddenLayer; $intIndexHidden1++)
      for($intIndexHidden2 = 0; $intIndexHidden2 < $this->intNumberNeuronsOfHiddenLayer; $intIndexHidden2++)
      {
        $intXPosHidden1 = 100 + $this->intLayerDistance + $this->intLayerDistance * $intIndexLayer - $this->intLayerDistance;

        $intYPosHidden1 = $intYPosHiddenStart + $this->intNeuronDistance * $intIndexHidden1;

        $intXPosHidden2 = 100 + $this->intLayerDistance + $this->intLayerDistance * $intIndexLayer;

        $intYPosHidden2 = $intYPosHiddenStart + $this->intNeuronDistance * $intIndexHidden2;

        imageline($this->handleImage, $intXPosHidden1, $intYPosHidden1, $intXPosHidden2, $intYPosHidden2, $this->handleColorConnection);
      }
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawInputNeurons()
{
  $intYPosInputStart = $this->calculateYPosStart($this->intNumberInputs);

  for($intIndex = 0; $intIndex < $this->intNumberInputs; $intIndex++)
  {
    imagefilledellipse($this->handleImage, 100, $intYPosInputStart + $this->intNeuronDistance * $intIndex, 30, 30, $this->handleColorNeuronInput);

    imageellipse($this->handleImage, 100, $intYPosInputStart + $this->intNeuronDistance * $intIndex, 30, 30, $this->handleColorNeuronBorder);
  }
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawHiddenNeurons()
{
  $intYPosHiddenStart = $this->calculateYPosStart($this->intNumberNeuronsOfHiddenLayer);

  for($intIndexLayer = 0; $intIndexLayer < $this->intNumberHiddenLayers; $intIndexLayer++)
    for($intIndexNeuron = 0; $intIndexNeuron < $this->intNumberNeuronsOfHiddenLayer; $intIndexNeuron++)
    {
      imagefilledellipse($this->handleImage, 100 + $this->intLayerDistance + $this->intLayerDistance * $intIndexLayer, $intYPosHiddenStart + $this->intNeuronDistance * $intIndexNeuron, 30, 30, $this->handleColorNeuronHidden);

      imageellipse($this->handleImage, 100 + $this->intLayerDistance + $this->intLayerDistance * $intIndexLayer, $intYPosHiddenStart + $this->intNeuronDistance * $intIndexNeuron, 30, 30, $this->handleColorNeuronBorder);
    }
}

// ****************************************************************************

/**
 * @uses calculateYPosStart()
 */

protected function drawOutputNeurons()
{
  for($layer = 0; $layer < $this->intNumberHiddenLayers; $layer++)
    $xpos = 100 + $this->intLayerDistance + $this->intLayerDistance * $layer;

  $yposStart = $this->calculateYPosStart($this->intNumberOfOutputs);

  for($intIndexNeuron = 0; $intIndexNeuron < $this->intNumberOfOutputs; $intIndexNeuron++)
  {
    imagefilledellipse($this->handleImage, $xpos + $this->intLayerDistance, $yposStart + $this->intNeuronDistance * $intIndexNeuron, 30, 30, $this->handleColorNeuronOutput);

    imageellipse($this->handleImage, $xpos + $this->intLayerDistance, $yposStart + $this->intNeuronDistance * $intIndexNeuron, 30, 30, $this->handleColorNeuronBorder);
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
  $this->handleImage = imagecreatetruecolor($this->calculateImageWidth(), $this->calculateImageHeight());

  $this->setColors();

  $this->setBackground();
}

// ****************************************************************************

protected function setColors()
{
  $this->handleColorBackground = imagecolorallocate($this->handleImage, 200, 200, 200);

  $this->handleColorNeuronInput = imagecolorallocate($this->handleImage, 0, 255, 0);

  $this->handleColorNeuronHidden = imagecolorallocate($this->handleImage, 255, 0, 0);

  $this->handleColorNeuronOutput = imagecolorallocate($this->handleImage, 0, 0, 255);

  $this->handleColorConnection = imagecolorallocate($this->handleImage, 155, 255, 155);

  $this->handleColorNeuronBorder = imagecolorallocate($this->handleImage, 0, 0, 0);
}

// ****************************************************************************

protected function setBackground()
{
  imagefill($this->handleImage, 0, 0, $this->handleColorBackground);
}

// ****************************************************************************

/**
 * Returns PNG image
 *
 * @return binary Image
 */

public function getImage()
{
  ob_start();

  imagepng($this->handleImage);

  $binReturn = ob_get_contents();

  ob_end_clean();

  return $binReturn;
}

// ****************************************************************************

/**
 * Print PNG image
 *
 * @uses getImage()
 */

public function printImage()
{
  header('Content-type: image/png');

  print $this->getImage();
}

// ****************************************************************************

/**
 * @param integer $intNumberNeurons
 * @return integer
 */

protected function calculateYPosStart($intNumberNeurons)
{
  $v1 = $this->intMaxNeuronsPerLayer * $this->intNeuronDistance / 2;

  $v2 = $intNumberNeurons * $this->intNeuronDistance / 2;

  return $v1 - $v2 + $this->intNeuronDistance;
}

// ****************************************************************************

/**
 * @return integer Pixel
 */

protected function calculateImageHeight()
{
  return (int)($this->intMaxNeuronsPerLayer * $this->intNeuronDistance + $this->intNeuronDistance);
}

// ****************************************************************************

/**
 * @return integer Pixel
 */

protected function calculateImageWidth()
{
  return (int)(($this->intNumberHiddenLayers + 2) * $this->intLayerDistance);
}

// ****************************************************************************

/**
 * Saves PNG image
 *
 * @param string $strFilename
 * @uses getImage()
 */

public function saveToFile($strFilename)
{
  file_put_contents($strFilename, $this->getImage());
}

// ****************************************************************************
}

?>
