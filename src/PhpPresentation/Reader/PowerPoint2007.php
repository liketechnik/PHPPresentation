<?php
/**
 * This file is part of PHPPresentation - A pure PHP library for reading and writing
 * presentations documents.
 *
 * PHPPresentation is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPPresentation/contributors.
 *
 * @link        https://github.com/PHPOffice/PHPPresentation
 * @copyright   2009-2015 PHPPresentation contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpPresentation\Reader;

use ZipArchive;
use PhpOffice\Common\XMLReader;
use PhpOffice\Common\Drawing as CommonDrawing;
use PhpOffice\Common\Microsoft\OLERead;
use PhpOffice\PhpPresentation\PhpPresentation;
use PhpOffice\PhpPresentation\Shape\Drawing\Gd;
use PhpOffice\PhpPresentation\Style\Bullet;
use PhpOffice\PhpPresentation\Style\Color;
use PhpOffice\PhpPresentation\Writer\PowerPoint2007\LayoutPack\TemplateBased;

/**
 * Serialized format reader
 */
class PowerPoint2007 extends AbstractReader implements ReaderInterface
{
    /**
     * Output Object
     * @var PhpPresentation
     */
    protected $oPhpPresentation;
    /**
     * Output Object
     * @var \ZipArchive
     */
    protected $oZip;
    /**
     * @var string[]
     */
    protected $arrayRels = array();
    /*
     * @var string
     */
    protected $filename;

    /**
     * Can the current \PhpOffice\PhpPresentation\Reader\ReaderInterface read the file?
     *
     * @param  string $pFilename
     * @throws \Exception
     * @return boolean
     */
    public function canRead($pFilename)
    {
        return $this->fileSupportsUnserializePhpPresentation($pFilename);
    }

    /**
     * Does a file support UnserializePhpPresentation ?
     *
     * @param  string $pFilename
     * @throws \Exception
     * @return boolean
     */
    public function fileSupportsUnserializePhpPresentation($pFilename = '')
    {
        // Check if file exists
        if (!file_exists($pFilename)) {
            throw new \Exception("Could not open " . $pFilename . " for reading! File does not exist.");
        }

        $oZip = new ZipArchive();
        // Is it a zip ?
        if ($oZip->open($pFilename) === true) {
            // Is it an OpenXML Document ?
            // Is it a Presentation ?
            if (is_array($oZip->statName('[Content_Types].xml')) && is_array($oZip->statName('ppt/presentation.xml'))) {
                return true;
            }
        } else {
            $oOLE = new OLERead();
            try {
                $oOLE->read($pFilename);
                return true;
            } catch (Exception $e) {
            }
        }

        return false;
    }

    /**
     * Loads PhpPresentation Serialized file
     *
     * @param  string $pFilename
     * @return \PhpOffice\PhpPresentation\PhpPresentation
     * @throws \Exception
     */
    public function load($pFilename)
    {
        // Unserialize... First make sure the file supports it!
        if (!$this->fileSupportsUnserializePhpPresentation($pFilename)) {
            throw new \Exception("Invalid file format for PhpOffice\PhpPresentation\Reader\PowerPoint2007: " . $pFilename . ".");
        }

        return $this->loadFile($pFilename);
    }

    /**
     * Load PhpPresentation Serialized file
     *
     * @param  string $pFilename
     * @return \PhpOffice\PhpPresentation\PhpPresentation
     */
    protected function loadFile($pFilename)
    {
        $this->oPhpPresentation = new PhpPresentation();
        $this->oPhpPresentation->removeSlideByIndex();
        $this->filename = $pFilename;

        $this->oZip = new ZipArchive();

        if ($this->oZip->open($this->filename) == ZipArchive::ER_NOZIP) {
            $this->loadEncryptedFile();
            return $this->oPhpPresentation;
        }

        $docPropsCore = $this->oZip->getFromName('docProps/core.xml');
        if ($docPropsCore !== false) {
            $this->loadDocumentProperties($docPropsCore);
        }

        $docPropsCustom = $this->oZip->getFromName('docProps/custom.xml');
        if ($docPropsCustom !== false) {
            $this->loadCustomProperties($docPropsCustom);
        }

        $pptViewProps = $this->oZip->getFromName('ppt/viewProps.xml');
        if ($pptViewProps !== false) {
            $this->loadViewProperties($pptViewProps);
        }

        $pptPresentation = $this->oZip->getFromName('ppt/presentation.xml');
        if ($pptPresentation !== false) {
            $this->loadSlides($pptPresentation);
        }

        return $this->oPhpPresentation;
    }

    /**
     * Read Document Properties
     * @param string $sPart
     */
    protected function loadDocumentProperties($sPart)
    {
        $xmlReader = new XMLReader();
        if ($xmlReader->getDomFromString($sPart)) {
            $arrayProperties = array(
                '/cp:coreProperties/dc:creator' => 'setCreator',
                '/cp:coreProperties/cp:lastModifiedBy' => 'setLastModifiedBy',
                '/cp:coreProperties/dc:title' => 'setTitle',
                '/cp:coreProperties/dc:description' => 'setDescription',
                '/cp:coreProperties/dc:subject' => 'setSubject',
                '/cp:coreProperties/cp:keywords' => 'setKeywords',
                '/cp:coreProperties/cp:category' => 'setCategory',
                '/cp:coreProperties/dcterms:created' => 'setCreated',
                '/cp:coreProperties/dcterms:modified' => 'setModified',
            );
            $oProperties = $this->oPhpPresentation->getProperties();
            foreach ($arrayProperties as $path => $property) {
                if (is_object($oElement = $xmlReader->getElement($path))) {
                    if ($oElement->hasAttribute('xsi:type') && $oElement->getAttribute('xsi:type') == 'dcterms:W3CDTF') {
                        $oDateTime = new \DateTime();
                        $oDateTime->createFromFormat(\DateTime::W3C, $oElement->nodeValue);
                        $oProperties->{$property}($oDateTime->getTimestamp());
                    } else {
                        $oProperties->{$property}($oElement->nodeValue);
                    }
                }
            }
        }
    }

    /**
     * Read Custom Properties
     * @param string $sPart
     */
    protected function loadCustomProperties($sPart)
    {
        $xmlReader = new XMLReader();
        $sPart = str_replace(' xmlns="http://schemas.openxmlformats.org/officeDocument/2006/custom-properties"', '',
            $sPart);
        if ($xmlReader->getDomFromString($sPart)) {
            $pathMarkAsFinal = '/Properties/property[@pid="2"][@fmtid="{D5CDD505-2E9C-101B-9397-08002B2CF9AE}"][@name="_MarkAsFinal"]/vt:bool';
            if (is_object($oElement = $xmlReader->getElement($pathMarkAsFinal))) {
                if ($oElement->nodeValue == 'true') {
                    $this->oPhpPresentation->markAsFinal(true);
                }
            }
        }
    }

    /**
     * Read View Properties
     * @param string $sPart
     */
    protected function loadViewProperties($sPart)
    {
        $xmlReader = new XMLReader();
        if ($xmlReader->getDomFromString($sPart)) {
            $pathZoom = '/p:viewPr/p:slideViewPr/p:cSldViewPr/p:cViewPr/p:scale/a:sx';
            if (is_object($oElement = $xmlReader->getElement($pathZoom))) {
                if ($oElement->hasAttribute('d') && $oElement->hasAttribute('n')) {
                    $this->oPhpPresentation->setZoom($oElement->getAttribute('n') / $oElement->getAttribute('d'));
                }
            }
        }
    }

    /**
     * Extract all slides
     */
    protected function loadSlides($sPart)
    {
        $xmlReader = new XMLReader();
        if ($xmlReader->getDomFromString($sPart)) {
            $fileRels = 'ppt/_rels/presentation.xml.rels';
            $this->loadRels($fileRels);
            foreach ($xmlReader->getElements('/p:presentation/p:sldIdLst/p:sldId') as $oElement) {
                $rId = $oElement->getAttribute('r:id');
                $pathSlide = isset($this->arrayRels[$fileRels][$rId]) ? $this->arrayRels[$fileRels][$rId]['Target'] : '';
                if (!empty($pathSlide)) {
                    $pptSlide = $this->oZip->getFromName('ppt/' . $pathSlide);
                    if ($pptSlide !== false) {
                        $this->loadRels('ppt/slides/_rels/' . basename($pathSlide) . '.rels');
                        $this->loadSlide($pptSlide, basename($pathSlide));
                    }
                }
            }
        }
    }

    /**
     * Extract data from slide
     * @param string $sPart
     * @param string $baseFile
     */
    protected function loadSlide($sPart, $baseFile)
    {
        $xmlReader = new XMLReader();
        if ($xmlReader->getDomFromString($sPart)) {
            // Core
            $this->oPhpPresentation->createSlide();
            $this->oPhpPresentation->setActiveSlideIndex($this->oPhpPresentation->getSlideCount() - 1);

            // Background
            $oElement = $xmlReader->getElement('/p:sld/p:cSld/p:bg/p:bgPr');
            if ($oElement) {
                $oElementColor = $xmlReader->getElement('a:solidFill/a:srgbClr', $oElement);
                if ($oElementColor) {
                    // Color
                    $oColor = new Color();
                    $oColor->setRGB($oElementColor->hasAttribute('val') ? $oElementColor->getAttribute('val') : null);
                    // Background
                    $oBackground = new \PhpOffice\PhpPresentation\Slide\Background\Color();
                    $oBackground->setColor($oColor);
                    // Slide Background
                    $oSlide = $this->oPhpPresentation->getActiveSlide();
                    $oSlide->setBackground($oBackground);
                }
                $oElementImage = $xmlReader->getElement('a:blipFill/a:blip', $oElement);
                if ($oElementImage) {
                    $relImg = $this->arrayRels['ppt/slides/_rels/' . $baseFile . '.rels'][$oElementImage->getAttribute('r:embed')];
                    if (is_array($relImg)) {
                        // File
                        $pathImage = 'ppt/slides/' . $relImg['Target'];
                        $pathImage = explode('/', $pathImage);
                        foreach ($pathImage as $key => $partPath) {
                            if ($partPath == '..') {
                                unset($pathImage[$key - 1]);
                                unset($pathImage[$key]);
                            }
                        }
                        $pathImage = implode('/', $pathImage);
                        $contentImg = $this->oZip->getFromName($pathImage);

                        $tmpBkgImg = tempnam(sys_get_temp_dir(), 'PhpPresentationReaderPpt2007Bkg');
                        file_put_contents($tmpBkgImg, $contentImg);
                        // Background
                        $oBackground = new \PhpOffice\PhpPresentation\Slide\Background\Image();
                        $oBackground->setPath($tmpBkgImg);
                        // Slide Background
                        $oSlide = $this->oPhpPresentation->getActiveSlide();
                        $oSlide->setBackground($oBackground);
                    }
                }
            }

            // Shapes
            foreach ($xmlReader->getElements('/p:sld/p:cSld/p:spTree/*') as $oNode) {
                switch ($oNode->tagName) {
                    case 'p:pic':
                        $this->loadShapeDrawing($xmlReader, $oNode, $baseFile);
                        break;
                    case 'p:sp':
                        $this->loadShapeRichText($xmlReader, $oNode, $baseFile);
                        break;
                    default:
                        //var_export($oNode->tagName);
                }
            }
            // Layout
            $oLayoutPack = new TemplateBased($this->filename);
            $oSlide = $this->oPhpPresentation->getActiveSlide();
            foreach ($this->arrayRels['ppt/slides/_rels/' . $baseFile . '.rels'] as $valueRel) {
                if ($valueRel['Type'] == 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/slideLayout') {
                    $layoutId = $valueRel['Target'];
                    $layoutId = str_replace('../slideLayouts/slideLayout', '', $layoutId);
                    $layoutId = str_replace('.xml', '', $layoutId);
                    $layoutName = $oLayoutPack->findLayoutName((int)$layoutId, $oSlide->getSlideMasterId());
                    $oSlide->setSlideLayout($layoutName);
                    break;
                }
            }
        }
    }

    /**
     *
     * @param XMLReader $document
     * @param \DOMElement $node
     * @param string $baseFile
     */
    protected function loadShapeDrawing(XMLReader $document, \DOMElement $node, $baseFile)
    {
        // Core
        $oShape = new Gd();
        $oShape->getShadow()->setVisible(false);
        // Variables
        $fileRels = 'ppt/slides/_rels/' . $baseFile . '.rels';

        $oElement = $document->getElement('p:nvPicPr/p:cNvPr', $node);
        if ($oElement) {
            $oShape->setName($oElement->hasAttribute('name') ? $oElement->getAttribute('name') : '');
            $oShape->setDescription($oElement->hasAttribute('descr') ? $oElement->getAttribute('descr') : '');
        }

        $oElement = $document->getElement('p:blipFill/a:blip', $node);
        if ($oElement) {
            if ($oElement->hasAttribute('r:embed') && isset($this->arrayRels[$fileRels][$oElement->getAttribute('r:embed')]['Target'])) {
                $pathImage = 'ppt/slides/' . $this->arrayRels[$fileRels][$oElement->getAttribute('r:embed')]['Target'];
                $pathImage = explode('/', $pathImage);
                foreach ($pathImage as $key => $partPath) {
                    if ($partPath == '..') {
                        unset($pathImage[$key - 1]);
                        unset($pathImage[$key]);
                    }
                }
                $pathImage = implode('/', $pathImage);
                $imageFile = $this->oZip->getFromName($pathImage);
                if (!empty($imageFile)) {
                    $oShape->setImageResource(imagecreatefromstring($imageFile));
                }
            }
        }

        $oElement = $document->getElement('p:spPr/a:xfrm', $node);
        if ($oElement) {
            if ($oElement->hasAttribute('rot')) {
                $oShape->setRotation(CommonDrawing::angleToDegrees($oElement->getAttribute('rot')));
            }
        }

        $oElement = $document->getElement('p:spPr/a:xfrm/a:off', $node);
        if ($oElement) {
            if ($oElement->hasAttribute('x')) {
                $oShape->setOffsetX(CommonDrawing::emuToPixels($oElement->getAttribute('x')));
            }
            if ($oElement->hasAttribute('y')) {
                $oShape->setOffsetY(CommonDrawing::emuToPixels($oElement->getAttribute('y')));
            }
        }

        $oElement = $document->getElement('p:spPr/a:xfrm/a:ext', $node);
        if ($oElement) {
            if ($oElement->hasAttribute('cx')) {
                $oShape->setWidth(CommonDrawing::emuToPixels($oElement->getAttribute('cx')));
            }
            if ($oElement->hasAttribute('cy')) {
                $oShape->setHeight(CommonDrawing::emuToPixels($oElement->getAttribute('cy')));
            }
        }

        $oElement = $document->getElement('p:spPr/a:effectLst', $node);
        if ($oElement) {
            $oShape->getShadow()->setVisible(true);

            $oSubElement = $document->getElement('a:outerShdw', $oElement);
            if ($oSubElement) {
                if ($oSubElement->hasAttribute('blurRad')) {
                    $oShape->getShadow()->setBlurRadius(CommonDrawing::emuToPixels($oSubElement->getAttribute('blurRad')));
                }
                if ($oSubElement->hasAttribute('dist')) {
                    $oShape->getShadow()->setDistance(CommonDrawing::emuToPixels($oSubElement->getAttribute('dist')));
                }
                if ($oSubElement->hasAttribute('dir')) {
                    $oShape->getShadow()->setDirection(CommonDrawing::angleToDegrees($oSubElement->getAttribute('dir')));
                }
                if ($oSubElement->hasAttribute('algn')) {
                    $oShape->getShadow()->setAlignment($oSubElement->getAttribute('algn'));
                }
            }

            $oSubElement = $document->getElement('a:outerShdw/a:srgbClr', $oElement);
            if ($oSubElement) {
                if ($oSubElement->hasAttribute('val')) {
                    $oColor = new Color();
                    $oColor->setRGB($oSubElement->getAttribute('val'));
                    $oShape->getShadow()->setColor($oColor);
                }
            }

            $oSubElement = $document->getElement('a:outerShdw/a:srgbClr/a:alpha', $oElement);
            if ($oSubElement) {
                if ($oSubElement->hasAttribute('val')) {
                    $oShape->getShadow()->setAlpha((int)$oSubElement->getAttribute('val') / 1000);
                }
            }
        }

        $this->oPhpPresentation->getActiveSlide()->addShape($oShape);
    }

    protected function loadShapeRichText(XMLReader $document, \DOMElement $node, $baseFile)
    {
        // Core
        $oShape = $this->oPhpPresentation->getActiveSlide()->createRichTextShape();
        $oShape->setParagraphs(array());
        // Variables
        $fileRels = 'ppt/slides/_rels/' . $baseFile . '.rels';

        $oElement = $document->getElement('p:spPr/a:xfrm', $node);
        if ($oElement && $oElement->hasAttribute('rot')) {
            $oShape->setRotation(CommonDrawing::angleToDegrees($oElement->getAttribute('rot')));
        }

        $oElement = $document->getElement('p:spPr/a:xfrm/a:off', $node);
        if ($oElement) {
            if ($oElement->hasAttribute('x')) {
                $oShape->setOffsetX(CommonDrawing::emuToPixels($oElement->getAttribute('x')));
            }
            if ($oElement->hasAttribute('y')) {
                $oShape->setOffsetY(CommonDrawing::emuToPixels($oElement->getAttribute('y')));
            }
        }

        $oElement = $document->getElement('p:spPr/a:xfrm/a:ext', $node);
        if ($oElement) {
            if ($oElement->hasAttribute('cx')) {
                $oShape->setWidth(CommonDrawing::emuToPixels($oElement->getAttribute('cx')));
            }
            if ($oElement->hasAttribute('cy')) {
                $oShape->setHeight(CommonDrawing::emuToPixels($oElement->getAttribute('cy')));
            }
        }

        $arrayElements = $document->getElements('p:txBody/a:p', $node);
        foreach ($arrayElements as $oElement) {
            // Core
            $oParagraph = $oShape->createParagraph();
            $oParagraph->setRichTextElements(array());

            $oSubElement = $document->getElement('a:pPr', $oElement);
            if ($oSubElement) {
                if ($oSubElement->hasAttribute('algn')) {
                    $oParagraph->getAlignment()->setHorizontal($oSubElement->getAttribute('algn'));
                }
                if ($oSubElement->hasAttribute('fontAlgn')) {
                    $oParagraph->getAlignment()->setVertical($oSubElement->getAttribute('fontAlgn'));
                }
                if ($oSubElement->hasAttribute('marL')) {
                    $oParagraph->getAlignment()->setMarginLeft(CommonDrawing::emuToPixels($oSubElement->getAttribute('marL')));
                }
                if ($oSubElement->hasAttribute('marR')) {
                    $oParagraph->getAlignment()->setMarginRight(CommonDrawing::emuToPixels($oSubElement->getAttribute('marR')));
                }
                if ($oSubElement->hasAttribute('indent')) {
                    $oParagraph->getAlignment()->setIndent(CommonDrawing::emuToPixels($oSubElement->getAttribute('indent')));
                }
                if ($oSubElement->hasAttribute('lvl')) {
                    $oParagraph->getAlignment()->setLevel($oSubElement->getAttribute('lvl'));
                }

                $oElementBuFont = $document->getElement('a:buFont', $oSubElement);
                $oParagraph->getBulletStyle()->setBulletType(Bullet::TYPE_NONE);
                if ($oElementBuFont) {
                    if ($oElementBuFont->hasAttribute('typeface')) {
                        $oParagraph->getBulletStyle()->setBulletFont($oElementBuFont->getAttribute('typeface'));
                    }
                }
                $oElementBuChar = $document->getElement('a:buChar', $oSubElement);
                if ($oElementBuChar) {
                    $oParagraph->getBulletStyle()->setBulletType(Bullet::TYPE_BULLET);
                    if ($oElementBuChar->hasAttribute('char')) {
                        $oParagraph->getBulletStyle()->setBulletChar($oElementBuChar->getAttribute('char'));
                    }
                }
                /*$oElementBuAutoNum = $document->getElement('a:buAutoNum', $oSubElement);
                if ($oElementBuAutoNum) {
                    $oParagraph->getBulletStyle()->setBulletType(Bullet::TYPE_NUMERIC);
                    if ($oElementBuAutoNum->hasAttribute('type')) {
                        $oParagraph->getBulletStyle()->setBulletNumericStyle($oElementBuAutoNum->getAttribute('type'));
                    }
                    if ($oElementBuAutoNum->hasAttribute('startAt') && $oElementBuAutoNum->getAttribute('startAt') != 1) {
                        $oParagraph->getBulletStyle()->setBulletNumericStartAt($oElementBuAutoNum->getAttribute('startAt'));
                    }
                }*/
            }
            $arraySubElements = $document->getElements('(a:r|a:br)', $oElement);
            foreach ($arraySubElements as $oSubElement) {
                if ($oSubElement->tagName == 'a:br') {
                    $oParagraph->createBreak();
                }
                if ($oSubElement->tagName == 'a:r') {
                    $oElementrPr = $document->getElement('a:rPr', $oSubElement);
                    if (is_object($oElementrPr)) {
                        $oText = $oParagraph->createTextRun();

                        if ($oElementrPr->hasAttribute('b')) {
                            $oText->getFont()->setBold($oElementrPr->getAttribute('b') == 'true' ? true : false);
                        }
                        if ($oElementrPr->hasAttribute('i')) {
                            $oText->getFont()->setItalic($oElementrPr->getAttribute('i') == 'true' ? true : false);
                        }
                        if ($oElementrPr->hasAttribute('strike')) {
                            $oText->getFont()->setStrikethrough($oElementrPr->getAttribute('strike') == 'noStrike' ? false : true);
                        }
                        if ($oElementrPr->hasAttribute('sz')) {
                            $oText->getFont()->setSize((int)($oElementrPr->getAttribute('sz') / 100));
                        }
                        if ($oElementrPr->hasAttribute('u')) {
                            $oText->getFont()->setUnderline($oElementrPr->getAttribute('u'));
                        }
                        // Color
                        $oElementSrgbClr = $document->getElement('a:solidFill/a:srgbClr', $oElementrPr);
                        if (is_object($oElementSrgbClr) && $oElementSrgbClr->hasAttribute('val')) {
                            $oColor = new Color();
                            $oColor->setRGB($oElementSrgbClr->getAttribute('val'));
                            $oText->getFont()->setColor($oColor);
                        }
                        // Hyperlink
                        $oElementHlinkClick = $document->getElement('a:hlinkClick', $oElementrPr);
                        if (is_object($oElementHlinkClick)) {
                            if ($oElementHlinkClick->hasAttribute('tooltip')) {
                                $oText->getHyperlink()->setTooltip($oElementHlinkClick->getAttribute('tooltip'));
                            }
                            if ($oElementHlinkClick->hasAttribute('r:id') && isset($this->arrayRels[$fileRels][$oElementHlinkClick->getAttribute('r:id')]['Target'])) {
                                $oText->getHyperlink()->setUrl($this->arrayRels[$fileRels][$oElementHlinkClick->getAttribute('r:id')]['Target']);
                            }
                        }
                        //} else {
                        // $oText = $oParagraph->createText();

                        $oSubSubElement = $document->getElement('a:t', $oSubElement);
                        $oText->setText($oSubSubElement->nodeValue);
                    }
                }
            }
        }

        if (count($oShape->getParagraphs()) > 0) {
            $oShape->setActiveParagraph(0);
        }
    }

    /**
     *
     * @param string $fileRels
     * @return string
     */
    protected function loadRels($fileRels)
    {
        $sPart = $this->oZip->getFromName($fileRels);
        if ($sPart !== false) {
            $xmlReader = new XMLReader();
            if ($xmlReader->getDomFromString($sPart)) {
                foreach ($xmlReader->getElements('*') as $oNode) {
                    $this->arrayRels[$fileRels][$oNode->getAttribute('Id')] = array(
                        'Target' => $oNode->getAttribute('Target'),
                        'Type' => $oNode->getAttribute('Type'),
                    );
                }
            }
        }
    }

    protected function loadEncryptedFile()
    {
        //return false;
        $oOLE = new OLERead();
        $oOLE->read($this->filename);

        $oStreamEncrypted = $oOLE->getStream($oOLE->encryptedPackage);
        $pos = 0;
        $size = self::getInt4d($oStreamEncrypted, $pos);
        var_dump($size);
        $pos += 8;
        $data = '';
        for ($inc = 0 ; $inc < $size ; $inc++) {
            $data .= pack('v', self::getInt1d($oStreamEncrypted, $pos + $inc));
        }

        $oStream = $oOLE->getStream($oOLE->encryptionInfo);
        $pos = 0;
        // EncryptionVersionInfo
        $vMajor = self::getInt2d($oStream, $pos);
        $pos += 2;
        $vMinor = self::getInt2d($oStream, $pos);
        $pos += 2;
        // EncryptionHeader.Flags
        $pos += 4;
        // EncryptionHeaderSize
        $size = self::getInt4d($oStream, $pos);
        $pos += 4;
        // EncryptionHeader
        // EncryptionHeader > Flags
        $flags = self::getInt4d($oStream, $pos);
        var_dump($flags);
        var_dump('EncryptionHeader > Flags > fCryptoAPI :' . (($flags >> 2) & bindec('1'))); //
        var_dump('EncryptionHeader > Flags > fDocProps :' . (($flags >> 3) & bindec('1'))); //
        var_dump('EncryptionHeader > Flags > fExternal :' . (($flags >> 4) & bindec('1'))); //
        var_dump('EncryptionHeader > Flags > fAES :' . (($flags >> 5) & bindec('1'))); //
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > SizeExtra
        $sizeExtra = self::getInt4d($oStream, $pos);
        var_dump('EncryptionHeader > SizeExtra : '.$sizeExtra);
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > AlgID
        $algID = self::getInt4d($oStream, $pos);
        var_dump('EncryptionHeader > AlgID :'.$algID.' => 128-bit AES : ' . var_export(0x0000660E, true));
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > AlgIDHash
        $algIDHash = self::getInt4d($oStream, $pos);
        var_dump('EncryptionHeader > AlgIDHash : '.$algIDHash. ' = 0x00008004');
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > KeySize
        $keySize = self::getInt4d($oStream, $pos);
        var_dump('EncryptionHeader > KeySize : '.$keySize. ' = 0x00000080');
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > ProviderType
        $providerType = self::getInt4d($oStream, $pos);
        var_dump('EncryptionHeader > ProviderType : '.$providerType. ' = 0x00000018');
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > Reserved1
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > Reserved2
        $pos += 4;
        $size -= 4;
        // EncryptionHeader > CSPName
        $CSPName = '';
        for ($inc = 0 ; $inc <= $size ; $inc += 2) {
            $chr = self::getInt2d($oStream, $pos);
            $pos += 2;
            if ($chr == 0) {
                break;
            }
            $CSPName .= chr($chr);
        }
        var_dump('EncryptionHeader > CSPName : '.$CSPName);
        // EncryptionVerifier
        // EncryptionVerifier > SaltSize
        $saltSize = self::getInt4d($oStream, $pos);
        var_dump('EncryptionVerifier > SaltSize : '.$saltSize.' = 0x00000010');
        $pos += 4;
        // EncryptionVerifier > Salt
        $salt = '';
        for ($inc = 0 ; $inc < 16 ; $inc ++) {
            $salt .= pack('v', self::getInt1d($oStream, $pos));
            $pos += 1;
        }
        var_dump('EncryptionVerifier > Salt : '.$salt);
        // EncryptionVerifier > EncryptedVerifier
        $encryptedVerifier = '';
        for ($inc = 0 ; $inc < 16 ; $inc ++) {
            $encryptedVerifier = pack('v', self::getInt1d($oStream, $pos));
            $pos += 1;
        }
        var_dump('EncryptionVerifier > EncryptedVerifier : '.$encryptedVerifier);
        // EncryptionVerifier > VerifierHashSize
        $verifierHashSize = self::getInt4d($oStream, $pos);
        var_dump('EncryptionVerifier > VerifierHashSize : '.$verifierHashSize.' = 0x00000010');
        $pos += 4;
        // EncryptionVerifier > EncryptedVerifierHash
        // mon cas : AES donc 32
        $encryptedVerifierHash = '';
        for ($inc = 0 ; $inc < 32 ; $inc ++) {
            $encryptedVerifierHash .= pack('v', self::getInt1d($oStream, $pos));
            $pos += 1;
        }
        var_dump($encryptedVerifierHash);

        $hash = $salt + $this->getPassword();
        for($inc = 0 ; $inc < 50000 ; $inc++) {
            $hash = sha1($hash);
        }
        $hash = sha1($hash . 0x00000000);
        var_dump($hash);
    }

    /**
     * Read 8-bit unsigned integer
     *
     * @param string $data
     * @param int $pos
     * @return int
     */
    public static function getInt1d($data, $pos)
    {
        return ord($data[$pos]);
    }

    /**
     * Read 16-bit unsigned integer
     *
     * @param string $data
     * @param int $pos
     * @return int
     */
    public static function getInt2d($data, $pos)
    {
        return ord($data[$pos]) | (ord($data[$pos+1]) << 8);
    }

    /**
     * Read 32-bit signed integer
     *
     * @param string $data
     * @param int $pos
     * @return int
     */
    public static function getInt4d($data, $pos)
    {
        // FIX: represent numbers correctly on 64-bit system
        // http://sourceforge.net/tracker/index.php?func=detail&aid=1487372&group_id=99160&atid=623334
        // Hacked by Andreas Rehm 2006 to ensure correct result of the <<24 block on 32 and 64bit systems
        $or24 = ord($data[$pos + 3]);
        if ($or24 >= 128) {
            // negative number
            $ord24 = -abs((256 - $or24) << 24);
        } else {
            $ord24 = ($or24 & 127) << 24;
        }
        return ord($data[$pos]) | (ord($data[$pos+1]) << 8) | (ord($data[$pos+2]) << 16) | $ord24;
    }
}
