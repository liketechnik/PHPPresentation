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

namespace PhpOffice\PhpPresentation\Shape;

use PhpOffice\PhpPresentation\AbstractShape;
use PhpOffice\PhpPresentation\ComparableInterface;

/**
 * AutoShape shape
 *
 * @link : https://github.com/scanny/python-pptx/blob/eaa1e0fd3db28b03a353e116a5c7d2084dd87c26/pptx/enum/shapes.py
 */
class AutoShape extends AbstractShape implements ComparableInterface
{
    const TYPE_10POINTSTAR = 'star10';
    const TYPE_12POINTSTAR = 'star12';
    const TYPE_16POINTSTAR = 'star16';
    const TYPE_24POINTSTAR = 'star24';
    const TYPE_32POINTSTAR = 'star32';
    const TYPE_4POINTSTAR = 'star4';
    const TYPE_5POINTSTAR = 'star5';
    const TYPE_6POINTSTAR = 'star6';
    const TYPE_7POINTSTAR = 'star7';
    const TYPE_8POINTSTAR = 'star8';
    const TYPE_ACTION_BUTTON_BACK_OR_PREVIOUS = 'actionButtonBackPrevious';
    const TYPE_ACTION_BUTTON_BEGINNING = 'actionButtonBeginning';
    const TYPE_ACTION_BUTTON_CUSTOM = 'actionButtonBlank';
    const TYPE_ACTION_BUTTON_DOCUMENT = 'actionButtonDocument';
    const TYPE_ACTION_BUTTON_END = 'actionButtonEnd';
    const TYPE_ACTION_BUTTON_FORWARD_OR_NEXT = 'actionButtonForwardNext';
    const TYPE_ACTION_BUTTON_HELP = 'actionButtonHelp';
    const TYPE_ACTION_BUTTON_HOME = 'actionButtonHome';
    const TYPE_ACTION_BUTTON_INFORMATION = 'actionButtonInformation';
    const TYPE_ACTION_BUTTON_MOVIE = 'actionButtonMovie';
    const TYPE_ACTION_BUTTON_RETURN = 'actionButtonReturn';
    const TYPE_ACTION_BUTTON_SOUND = 'actionButtonSound';
    const TYPE_ARC = 'arc';
    const TYPE_BALLOON = 'wedgeRoundRectCallout';
    const TYPE_BENTARROW = 'bentArrow';
    const TYPE_BENTUPARROW = 'bentUpArrow';
    const TYPE_BEVEL = 'bevel';
    const TYPE_BLOCKARC = 'blockArc';
    const TYPE_CAN = 'can';
    const TYPE_CHARTPLUS = 'chartPlus';
    const TYPE_CHARTSTAR = 'chartStar';
    const TYPE_CHARTX = 'chartX';
    const TYPE_CHEVRON = 'chevron';
    const TYPE_CHORD = 'chord';
    const TYPE_CIRCULARARROW = 'circularArrow';
    const TYPE_CLOUD = 'cloud';
    const TYPE_CLOUDCALLOUT = 'cloudCallout';
    const TYPE_CORNER = 'corner';
    const TYPE_CORNERTABS = 'cornerTabs';
    const TYPE_CROSS = 'plus';
    const TYPE_CUBE = 'cube';
    const TYPE_CURVEDDOWNARROW = 'curvedDownArrow';
    const TYPE_CURVEDDOWNRIBBON = 'ellipseRibbon';
    const TYPE_CURVEDLEFTARROW = 'curvedLeftArrow';
    const TYPE_CURVEDRIGHTARROW = 'curvedRightArrow';
    const TYPE_CURVEDUPARROW = 'curvedUpArrow';
    const TYPE_CURVEDUPRIBBON = 'ellipseRibbon2';
    const TYPE_DECAGON = 'decagon';
    const TYPE_DIAGONALSTRIPE = 'diagStripe';
    const TYPE_DIAMOND = 'diamond';
    const TYPE_DODECAGON = 'dodecagon';
    const TYPE_DONUT = 'donut';
    const TYPE_DOUBLEBRACE = 'bracePair';
    const TYPE_DOUBLEBRACKET = 'bracketPair';
    const TYPE_DOUBLEWAVE = 'doubleWave';
    const TYPE_DOWN_ARROW = 'downArrow';
    const TYPE_DOWN_ARROWCALLOUT = 'downArrowCallout';
    const TYPE_DOWN_RIBBON = 'ribbon';
    const TYPE_EXPLOSIONEXPLOSION1 = 'irregularSeal1';
    const TYPE_EXPLOSIONEXPLOSION2 = 'irregularSeal2';
    const TYPE_FLOWCHART_ALTERNATEPROCESS = 'flowChartAlternateProcess';
    const TYPE_FLOWCHART_CARD = 'flowChartPunchedCard';
    const TYPE_FLOWCHART_COLLATE = 'flowChartCollate';
    const TYPE_FLOWCHART_CONNECTOR = 'flowChartConnector';
    const TYPE_FLOWCHART_DATA = 'flowChartInputOutput';
    const TYPE_FLOWCHART_DECISION = 'flowChartDecision';
    const TYPE_FLOWCHART_DELAY = 'flowChartDelay';
    const TYPE_FLOWCHART_DIRECT_ACCESS_STORAGE = 'flowChartMagneticDrum';
    const TYPE_FLOWCHART_DISPLAY = 'flowChartDisplay';
    const TYPE_FLOWCHART_DOCUMENT = 'flowChartDocument';
    const TYPE_FLOWCHART_EXTRACT = 'flowChartExtract';
    const TYPE_FLOWCHART_INTERNAL_STORAGE = 'flowChartInternalStorage';
    const TYPE_FLOWCHART_MAGNETIC_DISK = 'flowChartMagneticDisk';
    const TYPE_FLOWCHART_MANUAL_INPUT = 'flowChartManualInput';
    const TYPE_FLOWCHART_MANUAL_OPERATION = 'flowChartManualOperation';
    const TYPE_FLOWCHART_MERGE = 'flowChartMerge';
    const TYPE_FLOWCHART_MULTIDOCUMENT = 'flowChartMultidocument';
    const TYPE_FLOWCHART_OFFLINE_STORAGE = 'flowChartOfflineStorage';
    const TYPE_FLOWCHART_OFFPAGE_CONNECTOR = 'flowChartOffpageConnector';
    const TYPE_FLOWCHART_OR = 'flowChartOr';
    const TYPE_FLOWCHART_PREDEFINED_PROCESS = 'flowChartPredefinedProcess';
    const TYPE_FLOWCHART_PREPARATION = 'flowChartPreparation';
    const TYPE_FLOWCHART_PROCESS = 'flowChartProcess';
    const TYPE_FLOWCHART_PUNCHEDTAPE = 'flowChartPunchedTape';
    const TYPE_FLOWCHART_SEQUENTIAL_ACCESS_STORAGE = 'flowChartMagneticTape';
    const TYPE_FLOWCHART_SORT = 'flowChartSort';
    const TYPE_FLOWCHART_STORED_DATA = 'flowChartOnlineStorage';
    const TYPE_FLOWCHART_SUMMING_JUNCTION = 'flowChartSummingJunction';
    const TYPE_FLOWCHART_TERMINATOR = 'flowChartTerminator';
    const TYPE_FOLDED_CORNER = 'foldedCorner';
    const TYPE_FRAME = 'frame';
    const TYPE_FUNNEL = 'funnel';
    const TYPE_GEAR_6 = 'gear6';
    const TYPE_GEAR_9 = 'gear9';
    const TYPE_HALF_FRAME = 'halfFrame';
    const TYPE_HEART = 'heart';
    const TYPE_HEPTAGON = 'heptagon';
    const TYPE_HEXAGON = 'hexagon';
    const TYPE_HORIZONTAL_SCROLL = 'horizontalScroll';
    const TYPE_ISOSCELES_TRIANGLE = 'triangle';
    const TYPE_LEFT_ARROW = 'leftArrow';
    const TYPE_LEFT_ARROW_CALLOUT = 'leftArrowCallout';
    const TYPE_LEFT_BRACE = 'leftBrace';
    const TYPE_LEFT_BRACKET = 'leftBracket';
    const TYPE_LEFT_CIRCULAR_ARROW = 'leftCircularArrow';
    const TYPE_LEFT_RIGHT_ARROW = 'leftRightArrow';
    const TYPE_LEFT_RIGHT_ARROW_CALLOUT = 'leftRightArrowCallout';
    const TYPE_LEFT_RIGHT_CIRCULAR_ARROW = 'leftRightCircularArrow';
    const TYPE_LEFT_RIGHT_RIBBON = 'leftRightRibbon';
    const TYPE_LEFT_RIGHT_UP_ARROW = 'leftRightUpArrow';
    const TYPE_LEFT_UP_ARROW = 'leftUpArrow';
    const TYPE_LIGHTNING_BOLT = 'lightningBolt';
    const TYPE_LINE_CALLOUT_1 = 'borderCallout1';
    const TYPE_LINE_CALLOUT_1_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_1_BORDER_AND_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_1_NO_BORDER = '';
    const TYPE_LINE_CALLOUT_2 = '';
    const TYPE_LINE_CALLOUT_2_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_2_BORDER_AND_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_2_NO_BORDER = '';
    const TYPE_LINE_CALLOUT_3 = '';
    const TYPE_LINE_CALLOUT_3_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_3_BORDER_AND_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_3_NO_BORDER = '';
    const TYPE_LINE_CALLOUT_4 = '';
    const TYPE_LINE_CALLOUT_4_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_4_BORDER_AND_ACCENT_BAR = '';
    const TYPE_LINE_CALLOUT_4_NO_BORDER = '';
    const TYPE_LINE_INVERSE = '';
    const TYPE_MATHDIVIDE = '';
    const TYPE_MATHEQUAL = '';
    const TYPE_MATHMINUS = '';
    const TYPE_MATHMULTIPLY = '';
    const TYPE_MATHNOTEQUAL = '';
    const TYPE_MATHPLUS = '';
    const TYPE_MIXED = '';
    const TYPE_MOON = '';
    const TYPE_NONISOSCELESTRAPEZOID = '';
    const TYPE_NOSYMBOL = '';
    const TYPE_NOTCHEDRIGHTARROW = '';
    const TYPE_NOTPRIMITIVE = '';
    const TYPE_OCTAGON = '';
    const TYPE_OVAL = '';
    const TYPE_OVALCALLOUT = '';
    const TYPE_PARALLELOGRAM = '';
    const TYPE_PENTAGON = '';
    const TYPE_PIE = '';
    const TYPE_PIEWEDGE = '';
    const TYPE_PLAQUE = '';
    const TYPE_PLAQUETABS = '';
    const TYPE_QUADARROW = '';
    const TYPE_QUADARROWCALLOUT = '';
    const TYPE_RECTANGLE = '';
    const TYPE_RECTANGULARCALLOUT = '';
    const TYPE_REGULARPENTAGON = '';
    const TYPE_RIGHTARROW = '';
    const TYPE_RIGHTARROWCALLOUT = '';
    const TYPE_RIGHTBRACE = '';
    const TYPE_RIGHTBRACKET = '';
    const TYPE_RIGHTTRIANGLE = '';
    const TYPE_ROUND1RECTANGLE = '';
    const TYPE_ROUND2DIAGRECTANGLE = '';
    const TYPE_ROUND2SAMERECTANGLE = '';
    const TYPE_ROUNDEDRECTANGLE = '';
    const TYPE_ROUNDEDRECTANGULARCALLOUT = '';
    const TYPE_SMILEYFACE = '';
    const TYPE_SNIP1RECTANGLE = '';
    const TYPE_SNIP2DIAGRECTANGLE = '';
    const TYPE_SNIP2SAMERECTANGLE = '';
    const TYPE_SNIPROUNDRECTANGLE = '';
    const TYPE_SQUARETABS = '';
    const TYPE_STRIPEDRIGHTARROW = '';
    const TYPE_SUN = '';
    const TYPE_SWOOSHARROW = '';
    const TYPE_TEAR = '';
    const TYPE_TRAPEZOID = '';
    const TYPE_UPARROW = '';
    const TYPE_UPARROWCALLOUT = '';
    const TYPE_UPDOWNARROW = '';
    const TYPE_UPDOWNARROWCALLOUT = '';
    const TYPE_UPRIBBON = '';
    const TYPE_UTURNARROW = '';
    const TYPE_VERTICALSCROLL = '';
    const TYPE_WAVE = '';

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $type;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     * @return AutoShape
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return AutoShape
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}
