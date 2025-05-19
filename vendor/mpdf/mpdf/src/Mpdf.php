<?php

namespace Mpdf;

use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Mpdf\Conversion;
use Mpdf\Css\Border;
use Mpdf\Css\TextVars;
use Mpdf\Log\Context as LogContext;
use Mpdf\Fonts\MetricsGenerator;
use Mpdf\Output\Destination;
use Mpdf\PsrLogAwareTrait\MpdfPsrLogAwareTrait;
use Mpdf\QrCode;
use Mpdf\Utils\Arrays;
use Mpdf\Utils\NumericString;
use Mpdf\Utils\UtfString;
use Psr\Log\NullLogger;

/**
 * mPDF, PHP library generating PDF files from UTF-8 encoded HTML
 *
 * based on FPDF by Olivier Plathey
 *      and HTML2FPDF by Renato Coelho
 *
 * @license GPL-2.0
 */
class Mpdf implements \Psr\Log\LoggerAwareInterface
{

	use Strict;
	use FpdiTrait;
	use MpdfPsrLogAwareTrait;

	const VERSION = '8.2.5';

	const SCALE = 72 / 25.4;

	const OBJECT_IDENTIFIER = "\xbb\xa4\xac";

	var $useFixedNormalLineHeight; // mPDF 6
	var $useFixedTextBaseline; // mPDF 6
	var $adjustFontDescLineheight; // mPDF 6
	var $interpolateImages; // mPDF 6
	var $defaultPagebreakType; // mPDF 6 pagebreaktype
	var $indexUseSubentries; // mPDF 6

	var $autoScriptToLang; // mPDF 6
	var $baseScript; // mPDF 6
	var $autoVietnamese; // mPDF 6
	var $autoArabic; // mPDF 6

	var $CJKforceend;
	var $h2bookmarks;
	var $h2toc;
	var $decimal_align;
	var $margBuffer;
	var $splitTableBorderWidth;

	var $bookmarkStyles;
	var $useActiveForms;

	var $repackageTTF;
	var $allowCJKorphans;
	var $allowCJKoverflow;

	var $useKerning;
	var $restrictColorSpace;
	var $bleedMargin;
	var $crossMarkMargin;
	var $cropMarkMargin;
	var $cropMarkLength;
	var $nonPrintMargin;

	var $PDFX;
	var $PDFXauto;

	var $PDFA;
	var $PDFAversion = '1-B';
	var $PDFAauto;
	var $ICCProfile;

	var $printers_info;
	var $iterationCounter;
	var $smCapsScale;
	var $smCapsStretch;

	var $backupSubsFont;
	var $backupSIPFont;
	var $fonttrans;
	var $debugfonts;
	var $useAdobeCJK;
	var $percentSubset;
	var $maxTTFFilesize;
	var $BMPonly;

	var $tableMinSizePriority;

	var $dpi;
	var $watermarkImgAlphaBlend;
	var $watermarkImgBehind;
	var $justifyB4br;
	var $packTableData;
	var $pgsIns;
	var $simpleTables;
	var $enableImports;

	var $debug;

	var $setAutoTopMargin;
	var $setAutoBottomMargin;
	var $autoMarginPadding;
	var $collapseBlockMargins;
	var $falseBoldWeight;
	var $normalLineheight;
	var $incrementFPR1;
	var $incrementFPR2;
	var $incrementFPR3;
	var $incrementFPR4;

	var $SHYlang;
	var $SHYleftmin;
	var $SHYrightmin;
	var $SHYcharmin;
	var $SHYcharmax;
	var $SHYlanguages;

	// PageNumber Conditional Text
	var $pagenumPrefix;
	var $pagenumSuffix;

	var $nbpgPrefix;
	var $nbpgSuffix;
	var $showImageErrors;
	var $allow_output_buffering;
	var $autoPadding;
	var $tabSpaces;
	var $autoLangToFont;
	var $watermarkTextAlpha;
	var $watermarkImageAlpha;
	var $watermark_size;
	var $watermark_pos;
	var $annotSize;
	var $annotMargin;
	var $annotOpacity;
	var $title2annots;
	var $keepColumns;
	var $keep_table_proportions;
	var $ignore_table_widths;
	var $ignore_table_percents;
	var $list_number_suffix;

	var $list_auto_mode; // mPDF 6
	var $list_indent_first_level; // mPDF 6
	var $list_indent_default; // mPDF 6
	var $list_indent_default_mpdf;
	var $list_marker_offset; // mPDF 6
	var $list_symbol_size;

	var $useSubstitutions;
	var $CSSselectMedia;

	var $forcePortraitHeaders;
	var $forcePortraitMargins;
	var $displayDefaultOrientation;
	var $ignore_invalid_utf8;
	var $allowedCSStags;
	var $onlyCoreFonts;
	var $allow_charset_conversion;

	var $jSWord;
	var $jSmaxChar;
	var $jSmaxCharLast;
	var $jSmaxWordLast;

	var $max_colH_correction;

	var $table_error_report;
	var $table_error_report_param;
	var $biDirectional;
	var $text_input_as_HTML;
	var $anchor2Bookmark;
	var $shrink_tables_to_fit;

	var $allow_html_optional_endtags;

	var $img_dpi;
	var $whitelistStreamWrappers;

	var $defaultheaderfontsize;
	var $defaultheaderfontstyle;
	var $defaultheaderline;
	var $defaultfooterfontsize;
	var $defaultfooterfontstyle;
	var $defaultfooterline;
	var $header_line_spacing;
	var $footer_line_spacing;

	var $pregCJKchars;
	var $pregRTLchars;
	var $pregCURSchars; // mPDF 6

	var $mirrorMargins;
	var $watermarkText;
	var $watermarkAngle;
	var $watermarkImage;
	var $showWatermarkText;
	var $showWatermarkImage;

	var $svgAutoFont;
	var $svgClasses;

	var $fontsizes;

	var $defaultPageNumStyle; // mPDF 6

	//////////////////////
	// INTERNAL VARIABLES
	//////////////////////
	var $extrapagebreak; // mPDF 6 pagebreaktype

	var $uniqstr; // mPDF 5.7.2
	var $hasOC;

	var $textvar; // mPDF 5.7.1
	var $fontLanguageOverride; // mPDF 5.7.1
	var $OTLtags; // mPDF 5.7.1
	var $OTLdata;  // mPDF 5.7.1

	var $useDictionaryLBR;
	var $useTibetanLBR;

	var $writingToC;
	var $layers;
	var $layerDetails;
	var $current_layer;
	var $open_layer_pane;
	var $decimal_offset;
	var $inMeter;

	var $CJKleading;
	var $CJKfollowing;
	var $CJKoverflow;

	var $textshadow;

	var $colsums;
	var $spanborder;
	var $spanborddet;

	var $visibility;

	var $kerning;
	var $fixedlSpacing;
	var $minwSpacing;
	var $lSpacingCSS;
	var $wSpacingCSS;

	var $spotColorIDs;
	var $SVGcolors;
	var $spotColors;
	var $defTextColor;
	var $defDrawColor;
	var $defFillColor;

	var $tableBackgrounds;
	var $inlineDisplayOff;
	var $kt_y00;
	var $kt_p00;
	var $upperCase;
	var $checkSIP;
	var $checkSMP;
	var $checkCJK;

	var $watermarkImgAlpha;
	var $PDFAXwarnings;

	var $MetadataRoot;
	var $OutputIntentRoot;
	var $InfoRoot;
	var $associatedFilesRoot;

	var $pdf_version;

	private $fontDir;

	var $tempDir;

	var $cacheCleanupInterval;

	var $allowAnnotationFiles;

	var $fontdata;

	var $noImageFile;
	var $lastblockbottommargin;
	var $baselineC;

	// mPDF 5.7.3  inline text-decoration parameters
	var $baselineSup;
	var $baselineSub;
	var $baselineS;
	var $baselineO;

	var $subPos;
	var $subArrMB;
	var $ReqFontStyle;
	var $tableClipPath;

	var $fullImageHeight;

	var $inFixedPosBlock;  // Internal flag for position:fixed block
	var $fixedPosBlock;  // Buffer string for position:fixed block
	var $fixedPosBlockDepth;
	var $fixedPosBlockBBox;
	var $fixedPosBlockSave;
	var $maxPosL;
	var $maxPosR;
	var $loaded;

	var $extraFontSubsets;

	var $docTemplateStart;  // Internal flag for page (page no. -1) that docTemplate starts on

	var $time0;

	var $hyphenationDictionaryFile;

	var $spanbgcolorarray;
	var $default_font;
	var $headerbuffer;
	var $lastblocklevelchange;
	var $nestedtablejustfinished;
	var $linebreakjustfinished;
	var $cell_border_dominance_L;
	var $cell_border_dominance_R;
	var $cell_border_dominance_T;
	var $cell_border_dominance_B;
	var $table_keep_together;
	var $plainCell_properties;
	var $shrin_k1;
	var $outerfilled;

	var $blockContext;
	var $floatDivs;

	var $patterns;
	var $pageBackgrounds;

	var $bodyBackgroundGradient;
	var $bodyBackgroundImage;
	var $bodyBackgroundColor;

	var $writingHTMLheader; // internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
	var $writingHTMLfooter;

	var $angle;

	var $gradients;

	var $kwt_Reference;
	var $kwt_BMoutlines;
	var $kwt_toc;

	var $tbrot_BMoutlines;
	var $tbrot_toc;

	var $col_BMoutlines;
	var $col_toc;

	var $floatbuffer;
	var $floatmargins;

	var $bullet;
	var $bulletarray;

	var $currentLang;
	var $default_lang;

	var $default_available_fonts;

	var $pageTemplate;
	var $docTemplate;
	var $docTemplateContinue;
	var $docTemplateContinue2pages;

	var $arabGlyphs;
	var $arabHex;
	var $persianGlyphs;
	var $persianHex;
	var $arabVowels;
	var $arabPrevLink;
	var $arabNextLink;

	var $formobjects; // array of Form Objects for WMF
	var $InlineProperties;
	var $InlineAnnots;
	var $InlineBDF; // mPDF 6 Bidirectional formatting
	var $InlineBDFctr; // mPDF 6

	var $ktAnnots;
	var $tbrot_Annots;
	var $kwt_Annots;
	var $columnAnnots;
	var $columnForms;
	var $tbrotForms;

	var $PageAnnots;

	var $pageDim; // Keep track of page wxh for orientation changes - set in _beginpage, used in _putannots

	var $breakpoints;

	var $tableLevel;
	var $tbctr;
	var $innermostTableLevel;
	var $saveTableCounter;
	var $cellBorderBuffer;

	var $saveHTMLFooter_height;
	var $saveHTMLFooterE_height;

	var $firstPageBoxHeader;
	var $firstPageBoxHeaderEven;
	var $firstPageBoxFooter;
	var $firstPageBoxFooterEven;

	var $page_box;

	var $show_marks; // crop or cross marks
	var $basepathIsLocal;

	var $use_kwt;
	var $kwt;
	var $kwt_height;
	var $kwt_y0;
	var $kwt_x0;
	var $kwt_buffer;
	var $kwt_Links;
	var $kwt_moved;
	var $kwt_saved;

	var $PageNumSubstitutions;

	var $table_borders_separate;
	var $base_table_properties;
	var $borderstyles;

	var $blockjustfinished;

	var $orig_bMargin;
	var $orig_tMargin;
	var $orig_lMargin;
	var $orig_rMargin;
	var $orig_hMargin;
	var $orig_fMargin;

	var $pageHTMLheaders;
	var $pageHTMLfooters;

	var $saveHTMLHeader;
	var $saveHTMLFooter;

	var $HTMLheaderPageLinks;
	var $HTMLheaderPageAnnots;
	var $HTMLheaderPageForms;

	// See Config\FontVariables for these next 5 values
	var $available_unifonts;
	var $sans_fonts;
	var $serif_fonts;
	var $mono_fonts;
	var $defaultSubsFont;

	// List of ALL available CJK fonts (incl. styles) (Adobe add-ons)  hw removed
	var $available_CJK_fonts;

	var $HTMLHeader;
	var $HTMLFooter;
	var $HTMLHeaderE;
	var $HTMLFooterE;
	var $bufferoutput;

	// CJK fonts
	var $Big5_widths;
	var $GB_widths;
	var $SJIS_widths;
	var $UHC_widths;

	// SetProtection
	var $encrypted;

	var $enc_obj_id; // encryption object id

	// Bookmark
	var $BMoutlines;
	var $OutlineRoot;

	// INDEX
	var $ColActive;
	var $Reference;
	var $CurrCol;
	var $NbCol;
	var $y0;   // Top ordinate of columns

	var $ColL;
	var $ColWidth;
	var $ColGap;

	// COLUMNS
	var $ColR;
	var $ChangeColumn;
	var $columnbuffer;
	var $ColDetails;
	var $columnLinks;
	var $colvAlign;

	// Substitutions
	var $substitute;  // Array of substitution strings e.g. <ttz>112</ttz>
	var $entsearch;  // Array of HTML entities (>ASCII 127) to substitute
	var $entsubstitute; // Array of substitution decimal unicode for the Hi entities

	// Default values if no style sheet offered	(cf. http://www.w3.org/TR/CSS21/sample.html)
	var $defaultCSS;
	var $defaultCssFile;

	var $lastoptionaltag; // Save current block item which HTML specifies optionsl endtag
	var $pageoutput;
	var $charset_in;
	var $blk;
	var $blklvl;
	var $ColumnAdjust;

	var $ws; // Word spacing

	var $HREF;
	var $pgwidth;
	var $fontlist;
	var $oldx;
	var $oldy;
	var $B;
	var $I;

	var $tdbegin;
	var $table;
	var $cell;
	var $col;
	var $row;

	var $divbegin;
	var $divwidth;
	var $divheight;
	var $spanbgcolor;

	// mPDF 6 Used for table cell (block-type) properties
	var $cellTextAlign;
	var $cellLineHeight;
	var $cellLineStackingStrategy;
	var $cellLineStackingShift;

	// mPDF 6  Lists
	var $listcounter;
	var $listlvl;
	var $listtype;
	var $listitem;

	var $pjustfinished;
	var $ignorefollowingspaces;
	var $SMALL;
	var $BIG;
	var $dash_on;
	var $dotted_on;

	var $textbuffer;
	var $currentfontstyle;
	var $currentfontfamily;
	var $currentfontsize;
	var $colorarray;
	var $bgcolorarray;
	var $internallink;
	var $enabledtags;

	var $lineheight;
	var $default_lineheight_correction;
	var $basepath;
	var $textparam;

	var $specialcontent;
	var $selectoption;
	var $objectbuffer;

	// Table Rotation
	var $table_rotate;
	var $tbrot_maxw;
	var $tbrot_maxh;
	var $tablebuffer;
	var $tbrot_align;
	var $tbrot_Links;

	var $keep_block_together; // Keep a Block from page-break-inside: avoid

	var $tbrot_y0;
	var $tbrot_x0;
	var $tbrot_w;
	var $tbrot_h;

	var $mb_enc;
	var $originalMbEnc;
	var $originalMbRegexEnc;

	var $directionality;

	var $extgstates; // Used for alpha channel - Transparency (Watermark)
	var $mgl;
	var $mgt;
	var $mgr;
	var $mgb;

	var $tts;
	var $ttz;
	var $tta;

	// Best to alter the below variables using default stylesheet above
	var $page_break_after_avoid;
	var $margin_bottom_collapse;
	var $default_font_size; // in pts
	var $original_default_font_size; // used to save default sizes when using table default
	var $original_default_font;
	var $watermark_font;
	var $defaultAlign;

	// TABLE
	var $defaultTableAlign;
	var $tablethead;
	var $thead_font_weight;
	var $thead_font_style;
	var $thead_font_smCaps;
	var $thead_valign_default;
	var $thead_textalign_default;
	var $tabletfoot;
	var $tfoot_font_weight;
	var $tfoot_font_style;
	var $tfoot_font_smCaps;
	var $tfoot_valign_default;
	var $tfoot_textalign_default;

	var $trow_text_rotate;

	var $cellPaddingL;
	var $cellPaddingR;
	var $cellPaddingT;
	var $cellPaddingB;
	var $table_border_attr_set;
	var $table_border_css_set;

	var $shrin_k; // factor with which to shrink tables - used internally - do not change
	var $shrink_this_table_to_fit; // 0 or false to disable; value (if set) gives maximum factor to reduce fontsize
	var $MarginCorrection; // corrects for OddEven Margins
	var $margin_footer;
	var $margin_header;

	var $tabletheadjustfinished;
	var $usingCoreFont;
	var $charspacing;

	var $js;

	/**
	 * Set timeout for cURL
	 *
	 * @var int
	 */
	var $curlTimeout;

	/**
	 * Set execution timeout for cURL
	 *
	 * @var int
	 */
	var $curlExecutionTimeout;

	/**
	 * Set to true to follow redirects with cURL.
	 *
	 * @var bool
	 */
	var $curlFollowLocation;

	/**
	 * Set your own CA certificate store for SSL Certificate verification when using cURL
	 *
	 * Useful setting to use on hosts with outdated CA certificates.
	 *
	 * Download the latest CA certificate from https://curl.haxx.se/docs/caextract.html
	 *
	 * @var string The absolute path to the pem file
	 */
	var $curlCaCertificate;

	/**
	 * Set to true to allow unsafe SSL HTTPS requests.
	 *
	 * Can be useful when using CDN with HTTPS and if you don't want to configure settings with SSL certificates.
	 *
	 * @var bool
	 */
	var $curlAllowUnsafeSslRequests;

	/**
	 * Set the proxy for cURL.
	 *
	 * @see https://curl.haxx.se/libcurl/c/CURLOPT_PROXY.html
	 *
	 * @var string
	 */
	var $curlProxy;

	/**
	 * Set the proxy auth for cURL.
	 *
	 * @see https://curl.haxx.se/libcurl/c/CURLOPT_PROXYUSERPWD.html
	 *
	 * @var string
	 */
	var $curlProxyAuth;

	/**
	 * Set the User-Agent header in the HTTP requests sent by cURL.
	 *
	 * @see https://curl.haxx.se/libcurl/c/CURLOPT_USERAGENT.html
	 *
	 * @var string User Agent header
	 */
	var $curlUserAgent;

	// Private properties FROM FPDF
	var $DisplayPreferences;
	var $flowingBlockAttr;

	var $page; // current page number

	var $n; // current object number
	var $n_js; // current object number

	var $n_ocg_hidden;
	var $n_ocg_print;
	var $n_ocg_view;

	var $offsets; // array of object offsets
	var $buffer; // buffer holding in-memory PDF
	var $pages; // array containing pages
	var $state; // current document state
	var $compress; // compression flag

	var $DefOrientation; // default orientation
	var $CurOrientation; // current orientation
	var $OrientationChanges; // array indicating orientation changes

	var $fwPt;
	var $fhPt; // dimensions of page format in points
	var $fw;
	var $fh; // dimensions of page format in user unit
	var $wPt;
	var $hPt; // current dimensions of page in points

	var $w;
	var $h; // current dimensions of page in user unit

	var $lMargin; // left margin
	var $tMargin; // top margin
	var $rMargin; // right margin
	var $bMargin; // page break margin
	var $cMarginL; // cell margin Left
	var $cMarginR; // cell margin Right
	var $cMarginT; // cell margin Left
	var $cMarginB; // cell margin Right

	var $DeflMargin; // Default left margin
	var $DefrMargin; // Default right margin

	var $x;
	var $y; // current position in user unit for cell positioning

	var $lasth; // height of last cell printed
	var $LineWidth; // line width in user unit

	var $CoreFonts; // array of standard font names
	var $fonts; // array of used fonts
	var $FontFiles; // array of font files

	var $images; // array of used images
	var $imageVars = []; // array of image vars

	var $PageLinks; // array of links in pages
	var $links; // array of internal links
	var $FontFamily; // current font family
	var $FontStyle; // current font style
	var $CurrentFont; // current font info
	var $FontSizePt; // current font size in points
	var $FontSize; // current font size in user unit
	var $DrawColor; // commands for drawing color
	var $FillColor; // commands for filling color
	var $TextColor; // commands for text color
	var $ColorFlag; // indicates whether fill and text colors are different
	var $autoPageBreak; // automatic page breaking
	var $PageBreakTrigger; // threshold used to trigger page breaks
	var $InFooter; // flag set when processing footer

	var $InHTMLFooter;
	var $processingFooter; // flag set when processing footer - added for columns
	var $processingHeader; // flag set when processing header - added for columns
	var $ZoomMode; // zoom display mode
	var $LayoutMode; // layout display mode
	var $title; // title
	var $subject; // subject
	var $author; // author
	var $keywords; // keywords
	var $creator; // creator

	var $customProperties; // array of custom document properties

	var $associatedFiles; // associated files (see SetAssociatedFiles below)
	var $additionalXmpRdf; // additional rdf added in xmp

	var $aliasNbPg; // alias for total number of pages
	var $aliasNbPgGp; // alias for total number of pages in page group

	var $ispre;
	var $outerblocktags;
	var $innerblocktags;

	public $exposeVersion;

	private $preambleWritten = false;

	private $watermarkTextObject;
	private $watermarkImageObject;

	/**
	 * @var string
	 */
	private $fontDescriptor;

	/**
	 * @var \Mpdf\Otl
	 */
	private $otl;

	/**
	 * @var \Mpdf\CssManager
	 */
	private $cssManager;

	/**
	 * @var \Mpdf\Gradient
	 */
	private $gradient;

	/**
	 * @var \Mpdf\Image\Bmp
	 */
	private $bmp;

	/**
	 * @var \Mpdf\Image\Wmf
	 */
	private $wmf;

	/**
	 * @var \Mpdf\TableOfContents
	 */
	private $tableOfContents;

	/**
	 * @var \Mpdf\Form
	 */
	private $form;

	/**
	 * @var \Mpdf\DirectWrite
	 */
	private $directWrite;

	/**
	 * @var \Mpdf\Cache
	 */
	private $cache;

	/**
	 * @var \Mpdf\Fonts\FontCache
	 */
	private $fontCache;

	/**
	 * @var \Mpdf\Fonts\FontFileFinder
	 */
	private $fontFileFinder;

	/**
	 * @var \Mpdf\Tag
	 */
	private $tag;

	/**
	 * @var \Mpdf\Barcode
	 * @todo solve Tag dependency and make private
	 */
	public $barcode;

	/**
	 * @var \Mpdf\QrCode\QrCode
	 */
	private $qrcode;

	/**
	 * @var \Mpdf\SizeConverter
	 */
	private $sizeConverter;

	/**
	 * @var \Mpdf\Color\ColorConverter
	 */
	private $colorConverter;

	/**
	 * @var \Mpdf\Color\ColorModeConverter
	 */
	private $colorModeConverter;

	/**
	 * @var \Mpdf\Color\ColorSpaceRestrictor
	 */
	private $colorSpaceRestrictor;

	/**
	 * @var \Mpdf\Hyphenator
	 */
	private $hyphenator;

	/**
	 * @var \Mpdf\Pdf\Protection
	 */
	private $protection;

	/**
	 * @var \Mpdf\Http\ClientInterface
	 */
	private $httpClient;

	/**
	 * @var \Mpdf\File\LocalContentLoaderInterface
	 */
	private $localContentLoader;

	/**
	 * @var \Mpdf\AssetFetcher
	 */
	private $assetFetcher;

	/**
	 * @var \Mpdf\Image\ImageProcessor
	 */
	private $imageProcessor;

	/**
	 * @var \Mpdf\Language\LanguageToFontInterface
	 */
	private $languageToFont;

	/**
	 * @var \Mpdf\Language\ScriptToLanguageInterface
	 */
	private $scriptToLanguage;

	/**
	 * @var \Mpdf\Writer\BaseWriter
	 */
	private $writer;

	/**
	 * @var \Mpdf\Writer\FontWriter
	 */
	private $fontWriter;

	/**
	 * @var \Mpdf\Writer\MetadataWriter
	 */
	private $metadataWriter;

	/**
	 * @var \Mpdf\Writer\ImageWriter
	 */
	private $imageWriter;

	/**
	 * @var \Mpdf\Writer\FormWriter
	 */
	private $formWriter;

	/**
	 * @var \Mpdf\Writer\PageWriter
	 */
	private $pageWriter;

	/**
	 * @var \Mpdf\Writer\BookmarkWriter
	 */
	private $bookmarkWriter;

	/**
	 * @var \Mpdf\Writer\OptionalContentWriter
	 */
	private $optionalContentWriter;

	/**
	 * @var \Mpdf\Writer\ColorWriter
	 */
	private $colorWriter;

	/**
	 * @var \Mpdf\Writer\BackgroundWriter
	 */
	private $backgroundWriter;

	/**
	 * @var \Mpdf\Writer\JavaScriptWriter
	 */
	private $javaScriptWriter;

	/**
	 * @var \Mpdf\Writer\ResourceWriter
	 */
	private $resourceWriter;

	/**
	 * @var string[]
	 */
	private $services;

	/**
	 * @var \Mpdf\Container\ContainerInterface
	 */
	private $container;

	/**
	 * @param mixed[] $config
	 * @param \Mpdf\Container\ContainerInterface|null $container Experimental container to override internal services
	 */
	public function __construct(array $config = [], $container = null)
	{
		$this->_dochecks();

		assert(!$container || $container instanceof \Mpdf\Container\ContainerInterface);

		list(
			$mode,
			$format,
			$default_font_size,
			$default_font,
			$mgl,
			$mgr,
			$mgt,
			$mgb,
			$mgh,
			$mgf,
			$orientation
		) = $this->initConstructorParams($config);

		$this->logger = new NullLogger();

		$originalConfig = $config;
		$config = $this->initConfig($originalConfig);

		$serviceFactory = new ServiceFactory($container);
		$services = $serviceFactory->getServices(
			$this,
			$this->logger,
			$config,
			$this->languageToFont,
			$this->scriptToLanguage,
			$this->fontDescriptor,
			$this->bmp,
			$this->directWrite,
			$this->wmf
		);

		$this->container = $container;
		$this->services = [];

		foreach ($services as $key => $service) {
			$this->{$key} = $service;
			$this->services[] = $key;
		}

		$this->time0 = microtime(true);

		$this->writingToC = false;

		$this->layers = [];
		$this->current_layer = 0;
		$this->open_layer_pane = false;

		$this->visibility = 'visible';

		$this->tableBackgrounds = [];
		$this->uniqstr = '20110230'; // mPDF 5.7.2
		$this->kt_y00 = 0;
		$this->kt_p00 = 0;
		$this->BMPonly = [];
		$this->page = 0;
		$this->n = 2;
		$this->buffer = '';
		$this->objectbuffer = [];
		$this->pages = [];
		$this->OrientationChanges = [];
		$this->state = 0;
		$this->fonts = [];
		$this->FontFiles = [];
		$this->images = [];
		$this->links = [];
		$this->InFooter = false;
		$this->processingFooter = false;
		$this->processingHeader = false;
		$this->lasth = 0;
		$this->FontFamily = '';
		$this->FontStyle = '';
		$this->FontSizePt = 9;

		// Small Caps
		$this->inMeter = false;
		$this->decimal_offset = 0;

		$this->PDFAXwarnings = [];

		$this->defTextColor = $this->TextColor = $this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings), true);
		$this->defDrawColor = $this->DrawColor = $this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings), true);
		$this->defFillColor = $this->FillColor = $this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings), true);

		$this->upperCase = require __DIR__ . '/../data/upperCase.php';

		$this->extrapagebreak = true; // mPDF 6 pagebreaktype

		$this->ColorFlag = false;
		$this->extgstates = [];

		$this->mb_enc = 'windows-1252';
		$this->originalMbEnc = mb_internal_encoding();
		$this->originalMbRegexEnc = mb_regex_encoding();

		$this->directionality = 'ltr';
		$this->defaultAlign = 'L';
		$this->defaultTableAlign = 'L';

		$this->fixedPosBlockSave = [];
		$this->extraFontSubsets = 0;

		$this->blockContext = 1;
		$this->floatDivs = [];
		$this->DisplayPreferences = '';

		// Tiling patterns used for backgrounds
		$this->patterns = [];
		$this->pageBackgrounds = [];
		$this->gradients = [];

		// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
		$this->writingHTMLheader = false;
		// internal flag - used both for writing HTMLHeaders/Footers and FixedPos block
		$this->writingHTMLfooter = false;

		$this->kwt_Reference = [];
		$this->kwt_BMoutlines = [];
		$this->kwt_toc = [];

		$this->tbrot_BMoutlines = [];
		$this->tbrot_toc = [];

		$this->col_BMoutlines = [];
		$this->col_toc = [];

		$this->pgsIns = [];
		$this->PDFAXwarnings = [];
		$this->inlineDisplayOff = false;
		$this->lSpacingCSS = '';
		$this->wSpacingCSS = '';
		$this->fixedlSpacing = false;
		$this->minwSpacing = 0;

		// Baseline for text
		$this->baselineC = 0.35;

		// mPDF 5.7.3  inline text-decoration parameters
		// Sets default change in baseline for <sup> text as factor of preceeding fontsize
		// 0.35 has been recommended; 0.5 matches applications like MS Word
		$this->baselineSup = 0.5;

		// Sets default change in baseline for <sub> text as factor of preceeding fontsize
		$this->baselineSub = -0.2;
		// Sets default height for <strike> text as factor of fontsize
		$this->baselineS = 0.3;
		// Sets default height for overline text as factor of fontsize
		$this->baselineO = 1.1;

		$this->noImageFile = __DIR__ . '/../data/no_image.jpg';
		$this->subPos = 0;

		$this->fullImageHeight = false;
		$this->floatbuffer = [];
		$this->floatmargins = [];
		$this->formobjects = []; // array of Form Objects for WMF
		$this->InlineProperties = [];
		$this->InlineAnnots = [];
		$this->InlineBDF = []; // mPDF 6
		$this->InlineBDFctr = 0; // mPDF 6
		$this->tbrot_Annots = [];
		$this->kwt_Annots = [];
		$this->columnAnnots = [];
		$this->PageLinks = [];
		$this->OrientationChanges = [];
		$this->pageDim = [];
		$this->saveHTMLHeader = [];
		$this->saveHTMLFooter = [];
		$this->PageAnnots = [];
		$this->PageNumSubstitutions = [];
		$this->breakpoints = []; // used in columnbuffer
		$this->tableLevel = 0;
		$this->tbctr = []; // counter for nested tables at each level
		$this->page_box = new PageBox();
		$this->show_marks = ''; // crop or cross marks
		$this->kwt = false;
		$this->kwt_height = 0;
		$this->kwt_y0 = 0;
		$this->kwt_x0 = 0;
		$this->kwt_buffer = [];
		$this->kwt_Links = [];
		$this->kwt_moved = false;
		$this->kwt_saved = false;
		$this->PageNumSubstitutions = [];
		$this->base_table_properties = [];
		$this->borderstyles = ['inset', 'groove', 'outset', 'ridge', 'dotted', 'dashed', 'solid', 'double'];
		$this->tbrot_align = 'C';

		$this->pageHTMLheaders = [];
		$this->pageHTMLfooters = [];
		$this->HTMLheaderPageLinks = [];
		$this->HTMLheaderPageAnnots = [];

		$this->HTMLheaderPageForms = [];
		$this->columnForms = [];
		$this->tbrotForms = [];

		$this->pageoutput = [];

		$this->bufferoutput = false;

		$this->encrypted = false;

		$this->BMoutlines = [];
		$this->ColActive = 0;          // Flag indicating that columns are on (the index is being processed)
		$this->Reference = [];    // Array containing the references
		$this->CurrCol = 0;               // Current column number
		$this->ColL = [0];   // Array of Left pos of columns - absolute - needs Margin correction for Odd-Even
		$this->ColR = [0];   // Array of Right pos of columns - absolute pos - needs Margin correction for Odd-Even
		$this->ChangeColumn = 0;
		$this->columnbuffer = [];
		$this->ColDetails = [];  // Keeps track of some column details
		$this->columnLinks = [];  // Cross references PageLinks
		$this->substitute = [];  // Array of substitution strings e.g. <ttz>112</ttz>
		$this->entsearch = [];  // Array of HTML entities (>ASCII 127) to substitute
		$this->entsubstitute = []; // Array of substitution decimal unicode for the Hi entities
		$this->lastoptionaltag = '';
		$this->charset_in = '';
		$this->blk = [];
		$this->blklvl = 0;
		$this->tts = false;
		$this->ttz = false;
		$this->tta = false;
		$this->ispre = false;

		$this->checkSIP = false;
		$this->checkSMP = false;
		$this->checkCJK = false;

		$this->page_break_after_avoid = false;
		$this->margin_bottom_collapse = false;
		$this->tablethead = 0;
		$this->tabletfoot = 0;
		$this->table_border_attr_set = 0;
		$this->table_border_css_set = 0;
		$this->shrin_k = 1.0;
		$this->shrink_this_table_to_fit = 0;
		$this->MarginCorrection = 0;

		$this->tabletheadjustfinished = false;
		$this->usingCoreFont = false;
		$this->charspacing = 0;

		$this->autoPageBreak = true;

		$this->_setPageSize($format, $orientation);
		$this->DefOrientation = $orientation;

		$this->margin_header = $mgh;
		$this->margin_footer = $mgf;

		$bmargin = $mgb;

		$this->DeflMargin = $mgl;
		$this->DefrMargin = $mgr;

		$this->orig_tMargin = $mgt;
		$this->orig_bMargin = $bmargin;
		$this->orig_lMargin = $this->DeflMargin;
		$this->orig_rMargin = $this->DefrMargin;
		$this->orig_hMargin = $this->margin_header;
		$this->orig_fMargin = $this->margin_footer;

		if ($this->setAutoTopMargin == 'pad') {
			$mgt += $this->margin_header;
		}
		if ($this->setAutoBottomMargin == 'pad') {
			$mgb += $this->margin_footer;
		}

		// sets l r t margin
		$this->SetMargins($this->DeflMargin, $this->DefrMargin, $mgt);

		// Automatic page break
		// sets $this->bMargin & PageBreakTrigger
		$this->SetAutoPageBreak($this->autoPageBreak, $bmargin);

		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;

		// Interior cell margin (1 mm) ? not used
		$this->cMarginL = 1;
		$this->cMarginR = 1;

		// Line width (0.2 mm)
		$this->LineWidth = .567 / Mpdf::SCALE;

		// Enable all tags as default
		$this->DisableTags();
		// Full width display mode
		$this->SetDisplayMode(100); // fullwidth? 'fullpage'

		// Compression
		$this->SetCompression(true);
		// Set default display preferences
		$this->SetDisplayPreferences('');

		$this->initFontConfig($originalConfig);

		// Available fonts
		$this->available_unifonts = [];
		foreach ($this->fontdata as $f => $fs) {
			if (isset($fs['R']) && $fs['R']) {
				$this->available_unifonts[] = $f;
			}
			if (isset($fs['B']) && $fs['B']) {
				$this->available_unifonts[] = $f . 'B';
			}
			if (isset($fs['I']) && $fs['I']) {
				$this->available_unifonts[] = $f . 'I';
			}
			if (isset($fs['BI']) && $fs['BI']) {
				$this->available_unifonts[] = $f . 'BI';
			}
		}

		$this->default_available_fonts = $this->available_unifonts;

		$optcore = false;
		$onlyCoreFonts = false;
		if (preg_match('/([\-+])aCJK/i', $mode, $m)) {
			$mode = preg_replace('/([\-+])aCJK/i', '', $mode); // mPDF 6
			if ($m[1] == '+') {
				$this->useAdobeCJK = true;
			} else {
				$this->useAdobeCJK = false;
			}
		}

		if (strlen($mode) == 1) {
			if ($mode == 's') {
				$this->percentSubset = 100;
				$mode = '';
			} elseif ($mode == 'c') {
				$onlyCoreFonts = true;
				$mode = '';
			}
		} elseif (substr($mode, -2) == '-s') {
			$this->percentSubset = 100;
			$mode = substr($mode, 0, strlen($mode) - 2);
		} elseif (substr($mode, -2) == '-c') {
			$onlyCoreFonts = true;
			$mode = substr($mode, 0, strlen($mode) - 2);
		} elseif (substr($mode, -2) == '-x') {
			$optcore = true;
			$mode = substr($mode, 0, strlen($mode) - 2);
		}

		// Autodetect if mode is a language_country string (en-GB or en_GB or en)
		if ($mode && $mode != 'UTF-8') { // mPDF 6
			list ($coreSuitable, $mpdf_pdf_unifont) = $this->languageToFont->getLanguageOptions($mode, $this->useAdobeCJK);
			if ($coreSuitable && $optcore) {
				$onlyCoreFonts = true;
			}
			if ($mpdf_pdf_unifont) {  // mPDF 6
				$default_font = $mpdf_pdf_unifont;
			}
			$this->currentLang = $mode;
			$this->default_lang = $mode;
		}

		$this->onlyCoreFonts = $onlyCoreFonts;

		if ($this->onlyCoreFonts) {
			$this->setMBencoding('windows-1252'); // sets $this->mb_enc
		} else {
			$this->setMBencoding('UTF-8'); // sets $this->mb_enc
		}
		@mb_regex_encoding('UTF-8'); // required only for mb_ereg... and mb_split functions

		// Adobe CJK fonts
		$this->available_CJK_fonts = [
			'gb',
			'big5',
			'sjis',
			'uhc',
			'gbB',
			'big5B',
			'sjisB',
			'uhcB',
			'gbI',
			'big5I',
			'sjisI',
			'uhcI',
			'gbBI',
			'big5BI',
			'sjisBI',
			'uhcBI',
		];

		// Standard fonts
		$this->CoreFonts = [
			'ccourier' => 'Courier',
			'ccourierB' => 'Courier-Bold',
			'ccourierI' => 'Courier-Oblique',
			'ccourierBI' => 'Courier-BoldOblique',
			'chelvetica' => 'Helvetica',
			'chelveticaB' => 'Helvetica-Bold',
			'chelveticaI' => 'Helvetica-Oblique',
			'chelveticaBI' => 'Helvetica-BoldOblique',
			'ctimes' => 'Times-Roman',
			'ctimesB' => 'Times-Bold',
			'ctimesI' => 'Times-Italic',
			'ctimesBI' => 'Times-BoldItalic',
			'csymbol' => 'Symbol',
			'czapfdingbats' => 'ZapfDingbats'
		];

		$this->fontlist = [
			"ctimes",
			"ccourier",
			"chelvetica",
			"csymbol",
			"czapfdingbats"
		];

		// Substitutions
		$this->setHiEntitySubstitutions();

		if ($this->onlyCoreFonts) {
			$this->useSubstitutions = true;
			$this->SetSubstitutions();
		} else {
			$this->useSubstitutions = $config['useSubstitutions'];
		}

		if (file_exists($this->defaultCssFile)) {
			$css = file_get_contents($this->defaultCssFile);
			$this->cssManager->ReadCSS('<style> ' . $css . ' </style>');
		} else {
			throw new \Mpdf\MpdfException(sprintf('Unable to read default CSS file "%s"', $this->defaultCssFile));
		}

		if ($default_font == '') {
			if ($this->onlyCoreFonts) {
				if (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']), $this->mono_fonts)) {
					$default_font = 'ccourier';
				} elseif (in_array(strtolower($this->defaultCSS['BODY']['FONT-FAMILY']), $this->sans_fonts)) {
					$default_font = 'chelvetica';
				} else {
					$default_font = 'ctimes';
				}
			} else {
				$default_font = $this->defaultCSS['BODY']['FONT-FAMILY'];
			}
		}
		if (!$default_font_size) {
			$mmsize = $this->sizeConverter->convert($this->defaultCSS['BODY']['FONT-SIZE']);
			$default_font_size = $mmsize * (Mpdf::SCALE);
		}

		if ($default_font) {
			$this->SetDefaultFont($default_font);
		}
		if ($default_font_size) {
			$this->SetDefaultFontSize($default_font_size);
		}

		$this->SetLineHeight(); // lineheight is in mm

		$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
		$this->HREF = '';
		$this->oldy = -1;
		$this->B = 0;
		$this->I = 0;

		// mPDF 6  Lists
		$this->listlvl = 0;
		$this->listtype = [];
		$this->listitem = [];
		$this->listcounter = [];

		$this->tdbegin = false;
		$this->table = [];
		$this->cell = [];
		$this->col = -1;
		$this->row = -1;
		$this->cellBorderBuffer = [];

		$this->divbegin = false;
		// mPDF 6
		$this->cellTextAlign = '';
		$this->cellLineHeight = '';
		$this->cellLineStackingStrategy = '';
		$this->cellLineStackingShift = '';

		$this->divwidth = 0;
		$this->divheight = 0;
		$this->spanbgcolor = false;
		$this->spanborder = false;
		$this->spanborddet = [];

		$this->blockjustfinished = false;
		$this->ignorefollowingspaces = true; // in order to eliminate exceeding left-side spaces
		$this->dash_on = false;
		$this->dotted_on = false;
		$this->textshadow = '';

		$this->currentfontfamily = '';
		$this->currentfontsize = '';
		$this->currentfontstyle = '';
		$this->colorarray = ''; // mPDF 6
		$this->spanbgcolorarray = ''; // mPDF 6
		$this->textbuffer = [];
		$this->internallink = [];
		$this->basepath = "";

		$this->SetBasePath('');

		$this->textparam = [];

		$this->specialcontent = '';
		$this->selectoption = [];
	}

	public function cleanup()
	{
		mb_internal_encoding($this->originalMbEnc);
		@mb_regex_encoding($this->originalMbRegexEnc);

		// this will free up the readers, based on code from Setasign's FpdiTrait::cleanUp()
		foreach ($this->createdReaders as $id) {
			$this->readers[$id]->getParser()->getStreamReader()->cleanUp();
			unset($this->readers[$id]);
		}

		$this->createdReaders = [];
	}

	private function initConfig(array $config)
	{
		$configObject = new ConfigVariables();
		$defaults = $configObject->getDefaults();
		$config = array_intersect_key($config + $defaults, $defaults);

		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}

		return $config;
	}

	private function initConstructorParams(array $config)
	{
		$constructor = [
			'mode' => '',
			'format' => 'A4',
			'default_font_size' => 0,
			'default_font' => '',
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 16,
			'margin_bottom' => 16,
			'margin_header' => 9,
			'margin_footer' => 9,
			'orientation' => 'P',
		];

		foreach ($constructor as $key => $val) {
			if (isset($config[$key])) {
				$constructor[$key] = $config[$key];
			}
		}

		return array_values($constructor);
	}

	private function initFontConfig(array $config)
	{
		$configObject = new FontVariables();
		$defaults = $configObject->getDefaults();
		$config = array_intersect_key($config + $defaults, $defaults);
		foreach ($config as $var => $val) {
			$this->{$var} = $val;
		}

		return $config;
	}

	function _setPageSize($format, &$orientation)
	{
		if (is_string($format)) {

			if (empty($format)) {
				$format = 'A4';
			}

			// e.g. A4-L = A4 landscape, A4-P = A4 portrait
			$orientation = $orientation ?: 'P';
			if (preg_match('/([0-9a-zA-Z]*)-([P,L])/i', $format, $m)) {
				list(, $format, $orientation) = $m;
			}

			$format = PageFormat::getSizeFromName($format);

			$this->fwPt = $format[0];
			$this->fhPt = $format[1];

		} else {

			if (!$format[0] || !$format[1]) {
				throw new \Mpdf\MpdfException('Invalid page format: ' . $format[0] . ' ' . $format[1]);
			}

			$this->fwPt = $format[0] * Mpdf::SCALE;
			$this->fhPt = $format[1] * Mpdf::SCALE;
		}

		$this->fw = $this->fwPt / Mpdf::SCALE;
		$this->fh = $this->fhPt / Mpdf::SCALE;

		// Page orientation
		$orientation = strtolower($orientation);
		if ($orientation === 'p' || $orientation === 'portrait') {
			$orientation = 'P';
			$this->wPt = $this->fwPt;
			$this->hPt = $this->fhPt;
		} elseif ($orientation === 'l' || $orientation === 'landscape') {
			$orientation = 'L';
			$this->wPt = $this->fhPt;
			$this->hPt = $this->fwPt;
		} else {
			throw new \Mpdf\MpdfException('Incorrect orientation: ' . $orientation);
		}

		$this->CurOrientation = $orientation;

		$this->w = $this->wPt / Mpdf::SCALE;
		$this->h = $this->hPt / Mpdf::SCALE;
	}

	function RestrictUnicodeFonts($res)
	{
		// $res = array of (Unicode) fonts to restrict to: e.g. norasi|norasiB - language specific
		if (count($res)) { // Leave full list of available fonts if passed blank array
			$this->available_unifonts = $res;
		} else {
			$this->available_unifonts = $this->default_available_fonts;
		}
		if (count($this->available_unifonts) == 0) {
			$this->available_unifonts[] = $this->default_available_fonts[0];
		}
		$this->available_unifonts = array_values($this->available_unifonts);
	}

	function setMBencoding($enc)
	{
		if ($this->mb_enc != $enc) {
			$this->mb_enc = $enc;
			mb_internal_encoding($this->mb_enc);
		}
	}

	function SetMargins($left, $right, $top)
	{
		// Set left, top and right margins
		$this->lMargin = $left;
		$this->rMargin = $right;
		$this->tMargin = $top;
	}

	function ResetMargins()
	{
		// ReSet left, top margins
		if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation == 'P' && $this->CurOrientation == 'L') {
			if (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
				$this->tMargin = $this->orig_rMargin;
				$this->bMargin = $this->orig_lMargin;
			} else { // ODD	// OR NOT MIRRORING MARGINS/FOOTERS
				$this->tMargin = $this->orig_lMargin;
				$this->bMargin = $this->orig_rMargin;
			}
			$this->lMargin = $this->DeflMargin;
			$this->rMargin = $this->DefrMargin;
			$this->MarginCorrection = 0;
			$this->PageBreakTrigger = $this->h - $this->bMargin;
		} elseif (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
			$this->lMargin = $this->DefrMargin;
			$this->rMargin = $this->DeflMargin;
			$this->MarginCorrection = $this->DefrMargin - $this->DeflMargin;
		} else { // ODD	// OR NOT MIRRORING MARGINS/FOOTERS
			$this->lMargin = $this->DeflMargin;
			$this->rMargin = $this->DefrMargin;
			if ($this->mirrorMargins) {
				$this->MarginCorrection = $this->DeflMargin - $this->DefrMargin;
			}
		}
		$this->x = $this->lMargin;
	}

	function SetLeftMargin($margin)
	{
		// Set left margin
		$this->lMargin = $margin;
		if ($this->page > 0 and $this->x < $margin) {
			$this->x = $margin;
		}
	}

	function SetTopMargin($margin)
	{
		// Set top margin
		$this->tMargin = $margin;
	}

	function SetRightMargin($margin)
	{
		// Set right margin
		$this->rMargin = $margin;
	}

	function SetAutoPageBreak($auto, $margin = 0)
	{
		// Set auto page break mode and triggering margin
		$this->autoPageBreak = $auto;
		$this->bMargin = $margin;
		$this->PageBreakTrigger = $this->h - $margin;
	}

	function SetDisplayMode($zoom, $layout = 'continuous')
	{
		$allowedZoomModes = ['fullpage', 'fullwidth', 'real', 'default', 'none'];

		if (in_array($zoom, $allowedZoomModes, true) || is_numeric($zoom)) {
			$this->ZoomMode = $zoom;
		} else {
			throw new \Mpdf\MpdfException('Incorrect zoom display mode: ' . $zoom);
		}

		$allowedLayoutModes = ['single', 'continuous', 'two', 'twoleft', 'tworight', 'default'];

		if (in_array($layout, $allowedLayoutModes, true)) {
			$this->LayoutMode = $layout;
		} else {
			throw new \Mpdf\MpdfException('Incorrect layout display mode: ' . $layout);
		}
	}

	function SetCompression($compress)
	{
		// Set page compression
		if (function_exists('gzcompress')) {
			$this->compress = $compress;
		} else {
			$this->compress = false;
		}
	}

	function SetTitle($title)
	{
		// Title of document // Arrives as UTF-8
		$this->title = $title;
	}

	function SetSubject($subject)
	{
		// Subject of document
		$this->subject = $subject;
	}

	function SetAuthor($author)
	{
		// Author of document
		$this->author = $author;
	}

	function SetKeywords($keywords)
	{
		// Keywords of document
		$this->keywords = $keywords;
	}

	function SetCreator($creator)
	{
		// Creator of document
		$this->creator = $creator;
	}

	function AddCustomProperty($key, $value)
	{
		$this->customProperties[$key] = $value;
	}

	/**
	 * Set one or multiple associated file ("/AF" as required by PDF/A-3)
	 *
	 * param $files is an array of hash containing:
	 *   path: file path on FS
	 *   content: file content
	 *   name: file name (not necessarily the same as the file on FS)
	 *   mime (optional): file mime type (will show up as /Subtype in the PDF)
	 *   description (optional): file description
	 *   AFRelationship (optional): PDF/A-3 AFRelationship (e.g. "Alternative")
	 *
	 * e.g. to associate 1 file:
	 *     [[
	 *         'path' => 'tmp/1234.xml',
	 *         'content' => 'file content',
	 *         'name' => 'public_name.xml',
	 *         'mime' => 'text/xml',
	 *         'description' => 'foo',
	 *         'AFRelationship' => 'Alternative',
	 *     ]]
	 *
	 * @param mixed[] $files Array of arrays of associated files. See above
	 */
	function SetAssociatedFiles(array $files)
	{
		$this->associatedFiles = $files;
	}

	function SetAdditionalXmpRdf($s)
	{
		$this->additionalXmpRdf = $s;
	}

	function SetAnchor2Bookmark($x)
	{
		$this->anchor2Bookmark = $x;
	}

	public function AliasNbPages($alias = '{nb}')
	{
		// Define an alias for total number of pages
		$this->aliasNbPg = $alias;
	}

	public function AliasNbPageGroups($alias = '{nbpg}')
	{
		// Define an alias for total number of pages in a group
		$this->aliasNbPgGp = $alias;
	}

	function SetAlpha($alpha, $bm = 'Normal', $return = false, $mode = 'B')
	{
		// alpha: real value from 0 (transparent) to 1 (opaque)
		// bm:    blend mode, one of the following:
		//          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
		//          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
		// set alpha for stroking (CA) and non-stroking (ca) operations
		// mode determines F (fill) S (stroke) B (both)
		if (($this->PDFA || $this->PDFX) && $alpha != 1) {
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) {
				$this->PDFAXwarnings[] = "Image opacity must be 100% (Opacity changed to 100%)";
			}
			$alpha = 1;
		}
		$a = ['BM' => '/' . $bm];
		if ($mode == 'F' || $mode == 'B') {
			$a['ca'] = $alpha; // mPDF 5.7.2
		}
		if ($mode == 'S' || $mode == 'B') {
			$a['CA'] = $alpha; // mPDF 5.7.2
		}
		$gs = $this->AddExtGState($a);
		if ($return) {
			return sprintf('/GS%d gs', $gs);
		} else {
			$this->writer->write(sprintf('/GS%d gs', $gs));
		}
	}

	function AddExtGState($parms)
	{
		$n = count($this->extgstates);
		// check if graphics state already exists
		for ($i = 1; $i <= $n; $i++) {
			if (count($this->extgstates[$i]['parms']) == count($parms)) {
				$same = true;
				foreach ($this->extgstates[$i]['parms'] as $k => $v) {
					if (!isset($parms[$k]) || $parms[$k] != $v) {
						$same = false;
						break;
					}
				}
				if ($same) {
					return $i;
				}
			}
		}
		$n++;
		$this->extgstates[$n]['parms'] = $parms;
		return $n;
	}

	function SetVisibility($v)
	{
		if (($this->PDFA || $this->PDFX) && $this->visibility != 'visible') {
			$this->PDFAXwarnings[] = "Cannot set visibility to anything other than full when using PDFA or PDFX";
			return '';
		} elseif (!$this->PDFA && !$this->PDFX) {
			$this->pdf_version = '1.5';
		}
		if ($this->visibility != 'visible') {
			$this->writer->write('EMC');
			$this->hasOC = intval($this->hasOC);
		}
		if ($v == 'printonly') {
			$this->writer->write('/OC /OC1 BDC');
			$this->hasOC = ($this->hasOC | 1);
		} elseif ($v == 'screenonly') {
			$this->writer->write('/OC /OC2 BDC');
			$this->hasOC = ($this->hasOC | 2);
		} elseif ($v == 'hidden') {
			$this->writer->write('/OC /OC3 BDC');
			$this->hasOC = ($this->hasOC | 4);
		} elseif ($v != 'visible') {
			throw new \Mpdf\MpdfException('Incorrect visibility: ' . $v);
		}
		$this->visibility = $v;
	}

	function Open()
	{
		// Begin document
		if ($this->state == 0) {
			$this->state = 1;
			if (false === $this->preambleWritten) {
				$this->writer->write('%PDF-' . $this->pdf_version);
				$this->writer->write('%' . chr(226) . chr(227) . chr(207) . chr(211)); // 4 chars > 128 to show binary file
				$this->preambleWritten = true;
			}
		}
	}

	function Close()
	{
		// @log Closing last page

		// Terminate document
		if ($this->state == 3) {
			return;
		}

		if ($this->page == 0) {
			$this->AddPage($this->CurOrientation);
		}

		if (count($this->cellBorderBuffer)) {
			$this->printcellbuffer();
		}

		// *TABLES*
		if ($this->tablebuffer) {
			$this->printtablebuffer();
		}

		/* -- COLUMNS -- */

		if ($this->ColActive) {
			$this->SetColumns(0);
			$this->ColActive = 0;
			if (count($this->columnbuffer)) {
				$this->printcolumnbuffer();
			}
		}

		/* -- END COLUMNS -- */

		// BODY Backgrounds
		$s = '';

		$s .= $this->PrintBodyBackgrounds();
		$s .= $this->PrintPageBackgrounds();

		$this->pages[$this->page] = preg_replace(
			'/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/',
			"\n" . $s . "\n" . '\\1',
			$this->pages[$this->page]
		);

		$this->pageBackgrounds = [];

		if ($this->visibility != 'visible') {
			$this->SetVisibility('visible');
		}

		$this->EndLayer();

		if (!$this->tableOfContents->TOCmark) { // Page footer
			$this->InFooter = true;
			$this->Footer();
			$this->InFooter = false;
		}

		if ($this->tableOfContents->TOCmark || count($this->tableOfContents->m_TOC)) {
			$this->tableOfContents->insertTOC();
		}

		// Close page
		$this->_endpage();

		// Close document
		$this->_enddoc();
	}

	/* -- BACKGROUNDS -- */

	function _resizeBackgroundImage($imw, $imh, $cw, $ch, $resize, $repx, $repy, $pba = [], $size = [])
	{
		// pba is background positioning area (from CSS background-origin) may not always be set [x,y,w,h]
		// size is from CSS3 background-size - takes precendence over old resize
		// $w - absolute length or % or auto or cover | contain
		// $h - absolute length or % or auto or cover | contain
		if (isset($pba['w'])) {
			$cw = $pba['w'];
		}
		if (isset($pba['h'])) {
			$ch = $pba['h'];
		}

		$cw = $cw * Mpdf::SCALE;
		$ch = $ch * Mpdf::SCALE;
		if (empty($size) && !$resize) {
			return [$imw, $imh, $repx, $repy];
		}

		if (isset($size['w']) && $size['w']) {
			if ($size['w'] == 'contain') {
				// Scale the image, while preserving its intrinsic aspect ratio (if any),
				// to the largest size such that both its width and its height can fit inside the background positioning area.
				// Same as resize==3
				$h = $imh * $cw / $imw;
				$w = $cw;
				if ($h > $ch) {
					$w = $w * $ch / $h;
					$h = $ch;
				}
			} elseif ($size['w'] == 'cover') {
				// Scale the image, while preserving its intrinsic aspect ratio (if any),
				// to the smallest size such that both its width and its height can completely cover the background positioning area.
				$h = $imh * $cw / $imw;
				$w = $cw;
				if ($h < $ch) {
					$w = $w * $h / $ch;
					$h = $ch;
				}
			} else {
				if (stristr($size['w'], '%')) {
					$size['w'] = (float) $size['w'];
					$size['w'] /= 100;
					$size['w'] = ($cw * $size['w']);
				}
				if (stristr($size['h'], '%')) {
					$size['h'] = (float) $size['h'];
					$size['h'] /= 100;
					$size['h'] = ($ch * $size['h']);
				}
				if ($size['w'] == 'auto' && $size['h'] == 'auto') {
					$w = $imw;
					$h = $imh;
				} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
					$w = $imw * $size['h'] / $imh;
					$h = $size['h'];
				} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
					$h = $imh * $size['w'] / $imw;
					$w = $size['w'];
				} else {
					$w = $size['w'];
					$h = $size['h'];
				}
			}
			return [$w, $h, $repx, $repy];
		} elseif ($resize == 1 && $imw > $cw) {
			$h = $imh * $cw / $imw;
			return [$cw, $h, $repx, $repy];
		} elseif ($resize == 2 && $imh > $ch) {
			$w = $imw * $ch / $imh;
			return [$w, $ch, $repx, $repy];
		} elseif ($resize == 3) {
			$w = $imw;
			$h = $imh;
			if ($w > $cw) {
				$h = $h * $cw / $w;
				$w = $cw;
			}
			if ($h > $ch) {
				$w = $w * $ch / $h;
				$h = $ch;
			}
			return [$w, $h, $repx, $repy];
		} elseif ($resize == 4) {
			$h = $imh * $cw / $imw;
			return [$cw, $h, $repx, $repy];
		} elseif ($resize == 5) {
			$w = $imw * $ch / $imh;
			return [$w, $ch, $repx, $repy];
		} elseif ($resize == 6) {
			return [$cw, $ch, $repx, $repy];
		}
		return [$imw, $imh, $repx, $repy];
	}

	function SetBackground(&$properties, &$maxwidth)
	{
		if (isset($properties['BACKGROUND-ORIGIN']) && ($properties['BACKGROUND-ORIGIN'] == 'border-box' || $properties['BACKGROUND-ORIGIN'] == 'content-box')) {
			$origin = $properties['BACKGROUND-ORIGIN'];
		} else {
			$origin = 'padding-box';
		}

		if (isset($properties['BACKGROUND-SIZE'])) {
			if (stristr($properties['BACKGROUND-SIZE'], 'contain')) {
				$bsw = $bsh = 'contain';
			} elseif (stristr($properties['BACKGROUND-SIZE'], 'cover')) {
				$bsw = $bsh = 'cover';
			} else {
				$bsw = $bsh = 'auto';
				$sz = preg_split('/\s+/', trim($properties['BACKGROUND-SIZE']));
				if (count($sz) == 2) {
					$bsw = $sz[0];
					$bsh = $sz[1];
				} else {
					$bsw = $sz[0];
				}
				if (!stristr($bsw, '%') && !stristr($bsw, 'auto')) {
					$bsw = $this->sizeConverter->convert($bsw, $maxwidth, $this->FontSize);
				}
				if (!stristr($bsh, '%') && !stristr($bsh, 'auto')) {
					$bsh = $this->sizeConverter->convert($bsh, $maxwidth, $this->FontSize);
				}
			}
			$size = ['w' => $bsw, 'h' => $bsh];
		} else {
			$size = false;
		} // mPDF 6
		if (preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $properties['BACKGROUND-IMAGE'])) {
			return ['gradient' => $properties['BACKGROUND-IMAGE'], 'origin' => $origin, 'size' => $size];
		} else {
			$file = $properties['BACKGROUND-IMAGE'];
			$sizesarray = $this->Image($file, 0, 0, 0, 0, '', '', false, false, false, false, true);
			if (isset($sizesarray['IMAGE_ID'])) {
				$image_id = $sizesarray['IMAGE_ID'];
				$orig_w = $sizesarray['WIDTH'] * Mpdf::SCALE;  // in user units i.e. mm
				$orig_h = $sizesarray['HEIGHT'] * Mpdf::SCALE;  // (using $this->img_dpi)
				if (isset($properties['BACKGROUND-IMAGE-RESOLUTION'])) {
					if (preg_match('/from-image/i', $properties['BACKGROUND-IMAGE-RESOLUTION']) && isset($sizesarray['set-dpi']) && $sizesarray['set-dpi'] > 0) {
						$orig_w *= $this->img_dpi / $sizesarray['set-dpi'];
						$orig_h *= $this->img_dpi / $sizesarray['set-dpi'];
					} elseif (preg_match('/(\d+)dpi/i', $properties['BACKGROUND-IMAGE-RESOLUTION'], $m)) {
						$dpi = $m[1];
						if ($dpi > 0) {
							$orig_w *= $this->img_dpi / $dpi;
							$orig_h *= $this->img_dpi / $dpi;
						}
					}
				}
				$x_repeat = true;
				$y_repeat = true;
				if (isset($properties['BACKGROUND-REPEAT'])) {
					if ($properties['BACKGROUND-REPEAT'] == 'no-repeat' || $properties['BACKGROUND-REPEAT'] == 'repeat-x') {
						$y_repeat = false;
					}
					if ($properties['BACKGROUND-REPEAT'] == 'no-repeat' || $properties['BACKGROUND-REPEAT'] == 'repeat-y') {
						$x_repeat = false;
					}
				}
				$x_pos = 0;
				$y_pos = 0;
				if (isset($properties['BACKGROUND-POSITION'])) {
					$ppos = preg_split('/\s+/', $properties['BACKGROUND-POSITION']);
					$x_pos = $ppos[0];
					$y_pos = $ppos[1];
					if (!stristr($x_pos, '%')) {
						$x_pos = $this->sizeConverter->convert($x_pos, $maxwidth, $this->FontSize);
					}
					if (!stristr($y_pos, '%')) {
						$y_pos = $this->sizeConverter->convert($y_pos, $maxwidth, $this->FontSize);
					}
				}
				if (isset($properties['BACKGROUND-IMAGE-RESIZE'])) {
					$resize = $properties['BACKGROUND-IMAGE-RESIZE'];
				} else {
					$resize = 0;
				}
				if (isset($properties['BACKGROUND-IMAGE-OPACITY'])) {
					$opacity = $properties['BACKGROUND-IMAGE-OPACITY'];
				} else {
					$opacity = 1;
				}
				return ['image_id' => $image_id, 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $x_pos, 'y_pos' => $y_pos, 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'resize' => $resize, 'opacity' => $opacity, 'itype' => $sizesarray['itype'], 'origin' => $origin, 'size' => $size];
			}
		}
		return false;
	}

	/* -- END BACKGROUNDS -- */

	function PrintBodyBackgrounds()
	{
		$s = '';
		$clx = 0;
		$cly = 0;
		$clw = $this->w;
		$clh = $this->h;
		// If using bleed and trim margins in paged media
		if ($this->pageDim[$this->page]['outer_width_LR'] || $this->pageDim[$this->page]['outer_width_TB']) {
			$clx = $this->pageDim[$this->page]['outer_width_LR'] - $this->pageDim[$this->page]['bleedMargin'];
			$cly = $this->pageDim[$this->page]['outer_width_TB'] - $this->pageDim[$this->page]['bleedMargin'];
			$clw = $this->w - 2 * $clx;
			$clh = $this->h - 2 * $cly;
		}

		if ($this->bodyBackgroundColor) {
			$s .= 'q ' . $this->SetFColor($this->bodyBackgroundColor, true) . "\n";
			if ($this->bodyBackgroundColor[0] == 5) { // RGBa
				$s .= $this->SetAlpha(ord($this->bodyBackgroundColor[4]) / 100, 'Normal', true, 'F') . "\n";
			} elseif ($this->bodyBackgroundColor[0] == 6) { // CMYKa
				$s .= $this->SetAlpha(ord($this->bodyBackgroundColor[5]) / 100, 'Normal', true, 'F') . "\n";
			}
			$s .= sprintf('%.3F %.3F %.3F %.3F re f Q', ($clx * Mpdf::SCALE), ($cly * Mpdf::SCALE), $clw * Mpdf::SCALE, $clh * Mpdf::SCALE) . "\n";
		}

		/* -- BACKGROUNDS -- */
		if ($this->bodyBackgroundGradient) {
			$g = $this->gradient->parseBackgroundGradient($this->bodyBackgroundGradient);
			if ($g) {
				$s .= $this->gradient->Gradient($clx, $cly, $clw, $clh, (isset($g['gradtype']) ? $g['gradtype'] : null), $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
			}
		}
		if ($this->bodyBackgroundImage) {
			if (isset($this->bodyBackgroundImage['gradient']) && $this->bodyBackgroundImage['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $this->bodyBackgroundImage['gradient'])) {
				$g = $this->gradient->parseMozGradient($this->bodyBackgroundImage['gradient']);
				if ($g) {
					$s .= $this->gradient->Gradient($clx, $cly, $clw, $clh, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true);
				}
			} elseif ($this->bodyBackgroundImage['image_id']) { // Background pattern
				$n = count($this->patterns) + 1;
				// If using resize, uses TrimBox (not including the bleed)
				list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($this->bodyBackgroundImage['orig_w'], $this->bodyBackgroundImage['orig_h'], $clw, $clh, $this->bodyBackgroundImage['resize'], $this->bodyBackgroundImage['x_repeat'], $this->bodyBackgroundImage['y_repeat']);

				$this->patterns[$n] = ['x' => $clx, 'y' => $cly, 'w' => $clw, 'h' => $clh, 'pgh' => $this->h, 'image_id' => $this->bodyBackgroundImage['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $this->bodyBackgroundImage['x_pos'], 'y_pos' => $this->bodyBackgroundImage['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $this->bodyBackgroundImage['itype']];
				if (($this->bodyBackgroundImage['opacity'] > 0 || $this->bodyBackgroundImage['opacity'] === '0') && $this->bodyBackgroundImage['opacity'] < 1) {
					$opac = $this->SetAlpha($this->bodyBackgroundImage['opacity'], 'Normal', true);
				} else {
					$opac = '';
				}
				$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, ($clx * Mpdf::SCALE), ($cly * Mpdf::SCALE), $clw * Mpdf::SCALE, $clh * Mpdf::SCALE) . "\n";
			}
		}
		/* -- END BACKGROUNDS -- */
		return $s;
	}

	function _setClippingPath($clx, $cly, $clw, $clh)
	{
		$s = ' q 0 w '; // Line width=0
		$s .= sprintf('%.3F %.3F m ', ($clx) * Mpdf::SCALE, ($this->h - ($cly)) * Mpdf::SCALE); // start point TL before the arc
		$s .= sprintf('%.3F %.3F l ', ($clx) * Mpdf::SCALE, ($this->h - ($cly + $clh)) * Mpdf::SCALE); // line to BL
		$s .= sprintf('%.3F %.3F l ', ($clx + $clw) * Mpdf::SCALE, ($this->h - ($cly + $clh)) * Mpdf::SCALE); // line to BR
		$s .= sprintf('%.3F %.3F l ', ($clx + $clw) * Mpdf::SCALE, ($this->h - ($cly)) * Mpdf::SCALE); // line to TR
		$s .= sprintf('%.3F %.3F l ', ($clx) * Mpdf::SCALE, ($this->h - ($cly)) * Mpdf::SCALE); // line to TL
		$s .= ' W n '; // Ends path no-op & Sets the clipping path
		return $s;
	}

	function PrintPageBackgrounds($adjustmenty = 0)
	{
		$s = '';

		ksort($this->pageBackgrounds);

		foreach ($this->pageBackgrounds as $bl => $pbs) {

			foreach ($pbs as $pb) {

				if ((!isset($pb['image_id']) && !isset($pb['gradient'])) || isset($pb['shadowonly'])) { // Background colour or boxshadow

					if ($pb['z-index'] > 0) {
						$this->current_layer = $pb['z-index'];
						$s .= "\n" . '/OCBZ-index /ZI' . $pb['z-index'] . ' BDC' . "\n";
					}

					if ($pb['visibility'] != 'visible') {
						if ($pb['visibility'] == 'printonly') {
							$s .= '/OC /OC1 BDC' . "\n";
						} elseif ($pb['visibility'] == 'screenonly') {
							$s .= '/OC /OC2 BDC' . "\n";
						} elseif ($pb['visibility'] == 'hidden') {
							$s .= '/OC /OC3 BDC' . "\n";
						}
					}

					// Box shadow
					if (isset($pb['shadow']) && $pb['shadow']) {
						$s .= $pb['shadow'] . "\n";
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					$s .= 'q ' . $this->SetFColor($pb['col'], true) . "\n";

					if ($pb['col'] && $pb['col'][0] === '5') { // RGBa
						$s .= $this->SetAlpha(ord($pb['col'][4]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($pb['col'] && $pb['col'][0] === '6') { // CMYKa
						$s .= $this->SetAlpha(ord($pb['col'][5]) / 100, 'Normal', true, 'F') . "\n";
					}

					$s .= sprintf('%.3F %.3F %.3F %.3F re f Q', $pb['x'] * Mpdf::SCALE, ($this->h - $pb['y']) * Mpdf::SCALE, $pb['w'] * Mpdf::SCALE, -$pb['h'] * Mpdf::SCALE) . "\n";

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}

					if ($pb['visibility'] != 'visible') {
						$s .= 'EMC' . "\n";
					}

					if ($pb['z-index'] > 0) {
						$s .= "\n" . 'EMCBZ-index' . "\n";
						$this->current_layer = 0;
					}
				}
			}

			/* -- BACKGROUNDS -- */
			foreach ($pbs as $pb) {

				if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {

					if ($pb['z-index'] > 0) {
						$this->current_layer = $pb['z-index'];
						$s .= "\n" . '/OCGZ-index /ZI' . $pb['z-index'] . ' BDC' . "\n";
					}

					if ($pb['visibility'] != 'visible') {
						if ($pb['visibility'] == 'printonly') {
							$s .= '/OC /OC1 BDC' . "\n";
						} elseif ($pb['visibility'] == 'screenonly') {
							$s .= '/OC /OC2 BDC' . "\n";
						} elseif ($pb['visibility'] == 'hidden') {
							$s .= '/OC /OC3 BDC' . "\n";
						}
					}

				}

				if (isset($pb['gradient']) && $pb['gradient']) {

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					$s .= $this->gradient->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}

				} elseif (isset($pb['image_id']) && $pb['image_id']) { // Background Image

					$pb['y'] -= $adjustmenty;
					$pb['h'] += $adjustmenty;
					$n = count($this->patterns) + 1;

					list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat'], $pb['bpa'], $pb['size']);

					$this->patterns[$n] = ['x' => $pb['x'], 'y' => $pb['y'], 'w' => $pb['w'], 'h' => $pb['h'], 'pgh' => $this->h, 'image_id' => $pb['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $pb['x_pos'], 'y_pos' => $pb['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $pb['itype'], 'bpa' => $pb['bpa']];

					$x = $pb['x'] * Mpdf::SCALE;
					$y = ($this->h - $pb['y']) * Mpdf::SCALE;
					$w = $pb['w'] * Mpdf::SCALE;
					$h = -$pb['h'] * Mpdf::SCALE;

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					if ($this->writingHTMLfooter || $this->writingHTMLheader) { // Write each (tiles) image rather than use as a pattern

						$iw = $pb['orig_w'] / Mpdf::SCALE;
						$ih = $pb['orig_h'] / Mpdf::SCALE;

						$w = $pb['w'];
						$h = $pb['h'];
						$x0 = $pb['x'];
						$y0 = $pb['y'];

						if (isset($pb['bpa']) && $pb['bpa']) {
							$w = $pb['bpa']['w'];
							$h = $pb['bpa']['h'];
							$x0 = $pb['bpa']['x'];
							$y0 = $pb['bpa']['y'];
						}

						if (isset($pb['size']['w']) && $pb['size']['w']) {
							$size = $pb['size'];

							if ($size['w'] == 'contain') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest
								// size such that both its width and its height can fit inside the background positioning area.
								// Same as resize==3
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih > $pb['bpa']['h']) {
									$iw = $iw * $pb['bpa']['h'] / $ih;
									$ih = $pb['bpa']['h'];
								}
							} elseif ($size['w'] == 'cover') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest
								// size such that both its width and its height can completely cover the background positioning area.
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih < $pb['bpa']['h']) {
									$iw = $iw * $ih / $pb['bpa']['h'];
									$ih = $pb['bpa']['h'];
								}
							} else {

								if (NumericString::containsPercentChar($size['w'])) {
									$size['w'] = NumericString::removePercentChar($size['w']);
									$size['w'] /= 100;
									$size['w'] = ($pb['bpa']['w'] * $size['w']);
								}

								if (NumericString::containsPercentChar($size['h'])) {
									$size['h'] = NumericString::removePercentChar($size['h']);
									$size['h'] /= 100;
									$size['h'] = ($pb['bpa']['h'] * $size['h']);
								}

								if ($size['w'] == 'auto' && $size['h'] == 'auto') {
									$iw = $iw;
									$ih = $ih;
								} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
									$iw = $iw * $size['h'] / $ih;
									$ih = $size['h'];
								} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
									$ih = $ih * $size['w'] / $iw;
									$iw = $size['w'];
								} else {
									$iw = $size['w'];
									$ih = $size['h'];
								}
							}
						}

						// Number to repeat
						if ($pb['x_repeat']) {
							$nx = ceil($pb['w'] / $iw) + 1;
						} else {
							$nx = 1;
						}

						if ($pb['y_repeat']) {
							$ny = ceil($pb['h'] / $ih) + 1;
						} else {
							$ny = 1;
						}

						$x_pos = $pb['x_pos'];
						if (stristr($x_pos, '%')) {
							$x_pos = (float) $x_pos;
							$x_pos /= 100;
							$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos);
						}

						$y_pos = $pb['y_pos'];

						if (stristr($y_pos, '%')) {
							$y_pos = (float) $y_pos;
							$y_pos /= 100;
							$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos);
						}

						if ($nx > 1) {
							while ($x_pos > ($pb['x'] - $pb['bpa']['x'])) {
								$x_pos -= $iw;
							}
						}

						if ($ny > 1) {
							while ($y_pos > ($pb['y'] - $pb['bpa']['y'])) {
								$y_pos -= $ih;
							}
						}

						for ($xi = 0; $xi < $nx; $xi++) {
							for ($yi = 0; $yi < $ny; $yi++) {
								$x = $x0 + $x_pos + ($iw * $xi);
								$y = $y0 + $y_pos + ($ih * $yi);
								if ($pb['opacity'] > 0 && $pb['opacity'] < 1) {
									$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
								} else {
									$opac = '';
								}
								$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac, $iw * Mpdf::SCALE, $ih * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($y + $ih)) * Mpdf::SCALE, $pb['image_id']) . "\n";
							}
						}

					} else {
						if (($pb['opacity'] > 0 || $pb['opacity'] === '0') && $pb['opacity'] < 1) {
							$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
						} else {
							$opac = '';
						}
						$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) . "\n";
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				}

				if ((isset($pb['gradient']) && $pb['gradient']) || (isset($pb['image_id']) && $pb['image_id'])) {
					if ($pb['visibility'] != 'visible') {
						$s .= 'EMC' . "\n";
					}

					if ($pb['z-index'] > 0) {
						$s .= "\n" . 'EMCGZ-index' . "\n";
						$this->current_layer = 0;
					}
				}
			}
			/* -- END BACKGROUNDS -- */
		}

		return $s;
	}

	function PrintTableBackgrounds($adjustmenty = 0)
	{
		$s = '';
		/* -- BACKGROUNDS -- */
		ksort($this->tableBackgrounds);
		foreach ($this->tableBackgrounds as $bl => $pbs) {
			foreach ($pbs as $pb) {
				if ((!isset($pb['gradient']) || !$pb['gradient']) && (!isset($pb['image_id']) || !$pb['image_id'])) {
					$s .= 'q ' . $this->SetFColor($pb['col'], true) . "\n";
					if ($pb['col'][0] == 5) { // RGBa
						$s .= $this->SetAlpha(ord($pb['col'][4]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($pb['col'][0] == 6) { // CMYKa
						$s .= $this->SetAlpha(ord($pb['col'][5]) / 100, 'Normal', true, 'F') . "\n";
					}
					$s .= sprintf('%.3F %.3F %.3F %.3F re %s Q', $pb['x'] * Mpdf::SCALE, ($this->h - $pb['y']) * Mpdf::SCALE, $pb['w'] * Mpdf::SCALE, -$pb['h'] * Mpdf::SCALE, 'f') . "\n";
				}
				if (isset($pb['gradient']) && $pb['gradient']) {
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}
					$s .= $this->gradient->Gradient($pb['x'], $pb['y'], $pb['w'], $pb['h'], $pb['gradtype'], $pb['stops'], $pb['colorspace'], $pb['coords'], $pb['extend'], true);
					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				}
				if (isset($pb['image_id']) && $pb['image_id']) { // Background pattern
					$pb['y'] -= $adjustmenty;
					$pb['h'] += $adjustmenty;
					$n = count($this->patterns) + 1;
					list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($pb['orig_w'], $pb['orig_h'], $pb['w'], $pb['h'], $pb['resize'], $pb['x_repeat'], $pb['y_repeat']);
					$this->patterns[$n] = ['x' => $pb['x'], 'y' => $pb['y'], 'w' => $pb['w'], 'h' => $pb['h'], 'pgh' => $this->h, 'image_id' => $pb['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $pb['x_pos'], 'y_pos' => $pb['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $pb['itype']];
					$x = $pb['x'] * Mpdf::SCALE;
					$y = ($this->h - $pb['y']) * Mpdf::SCALE;
					$w = $pb['w'] * Mpdf::SCALE;
					$h = -$pb['h'] * Mpdf::SCALE;

					// mPDF 5.7.3
					if (($this->writingHTMLfooter || $this->writingHTMLheader) && (!isset($pb['clippath']) || $pb['clippath'] == '')) {
						// Set clipping path
						$pb['clippath'] = sprintf(' q 0 w %.3F %.3F m %.3F %.3F l %.3F %.3F l %.3F %.3F l %.3F %.3F l W n ', $x, $y, $x, $y + $h, $x + $w, $y + $h, $x + $w, $y, $x, $y);
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= $pb['clippath'] . "\n";
					}

					// mPDF 5.7.3
					if ($this->writingHTMLfooter || $this->writingHTMLheader) { // Write each (tiles) image rather than use as a pattern
						$iw = $pb['orig_w'] / Mpdf::SCALE;
						$ih = $pb['orig_h'] / Mpdf::SCALE;

						$w = $pb['w'];
						$h = $pb['h'];
						$x0 = $pb['x'];
						$y0 = $pb['y'];

						if (isset($pb['bpa']) && $pb['bpa']) {
							$w = $pb['bpa']['w'];
							$h = $pb['bpa']['h'];
							$x0 = $pb['bpa']['x'];
							$y0 = $pb['bpa']['y'];
						} // At present 'bpa' (background page area) is not set for tablebackgrounds - only pagebackgrounds
						// For now, just set it as:
						else {
							$pb['bpa'] = ['x' => $x0, 'y' => $y0, 'w' => $w, 'h' => $h];
						}

						if (isset($pb['size']['w']) && $pb['size']['w']) {
							$size = $pb['size'];

							if ($size['w'] == 'contain') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the largest size such that both its width and its height can fit inside the background positioning area.
								// Same as resize==3
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih > $pb['bpa']['h']) {
									$iw = $iw * $pb['bpa']['h'] / $ih;
									$ih = $pb['bpa']['h'];
								}
							} elseif ($size['w'] == 'cover') {
								// Scale the image, while preserving its intrinsic aspect ratio (if any), to the smallest size such that both its width and its height can completely cover the background positioning area.
								$ih = $ih * $pb['bpa']['w'] / $iw;
								$iw = $pb['bpa']['w'];
								if ($ih < $pb['bpa']['h']) {
									$iw = $iw * $ih / $pb['bpa']['h'];
									$ih = $pb['bpa']['h'];
								}
							} else {
								if (NumericString::containsPercentChar($size['w'])) {
									$size['w'] = NumericString::removePercentChar($size['w']);
									$size['w'] /= 100;
									$size['w'] = ($pb['bpa']['w'] * $size['w']);
								}
								if (NumericString::containsPercentChar($size['h'])) {
									$size['h'] = NumericString::removePercentChar($size['h']);
									$size['h'] /= 100;
									$size['h'] = ($pb['bpa']['h'] * $size['h']);
								}
								if ($size['w'] == 'auto' && $size['h'] == 'auto') {
									$iw = $iw;
									$ih = $ih;
								} elseif ($size['w'] == 'auto' && $size['h'] != 'auto') {
									$iw = $iw * $size['h'] / $ih;
									$ih = $size['h'];
								} elseif ($size['w'] != 'auto' && $size['h'] == 'auto') {
									$ih = $ih * $size['w'] / $iw;
									$iw = $size['w'];
								} else {
									$iw = $size['w'];
									$ih = $size['h'];
								}
							}
						}

						// Number to repeat
						if (isset($pb['x_repeat']) && $pb['x_repeat']) {
							$nx = ceil($pb['w'] / $iw) + 1;
						} else {
							$nx = 1;
						}
						if (isset($pb['y_repeat']) && $pb['y_repeat']) {
							$ny = ceil($pb['h'] / $ih) + 1;
						} else {
							$ny = 1;
						}

						$x_pos = $pb['x_pos'];
						if (NumericString::containsPercentChar($x_pos)) {
							$x_pos = NumericString::removePercentChar($x_pos);
							$x_pos /= 100;
							$x_pos = ($pb['bpa']['w'] * $x_pos) - ($iw * $x_pos);
						}
						$y_pos = $pb['y_pos'];
						if (NumericString::containsPercentChar($y_pos)) {
							$y_pos = NumericString::removePercentChar($y_pos);
							$y_pos /= 100;
							$y_pos = ($pb['bpa']['h'] * $y_pos) - ($ih * $y_pos);
						}
						if ($nx > 1) {
							while ($x_pos > ($pb['x'] - $pb['bpa']['x'])) {
								$x_pos -= $iw;
							}
						}
						if ($ny > 1) {
							while ($y_pos > ($pb['y'] - $pb['bpa']['y'])) {
								$y_pos -= $ih;
							}
						}
						for ($xi = 0; $xi < $nx; $xi++) {
							for ($yi = 0; $yi < $ny; $yi++) {
								$x = $x0 + $x_pos + ($iw * $xi);
								$y = $y0 + $y_pos + ($ih * $yi);
								if ($pb['opacity'] > 0 && $pb['opacity'] < 1) {
									$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
								} else {
									$opac = '';
								}
								$s .= sprintf("q %s %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $opac, $iw * Mpdf::SCALE, $ih * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($y + $ih)) * Mpdf::SCALE, $pb['image_id']) . "\n";
							}
						}
					} else {
						if (($pb['opacity'] > 0 || $pb['opacity'] === '0') && $pb['opacity'] < 1) {
							$opac = $this->SetAlpha($pb['opacity'], 'Normal', true);
						} else {
							$opac = '';
						}
						$s .= sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $x, $y, $w, $h) . "\n";
					}

					if (isset($pb['clippath']) && $pb['clippath']) {
						$s .= 'Q' . "\n";
					}
				}
			}
		}
		/* -- END BACKGROUNDS -- */
		return $s;
	}

	function BeginLayer($id)
	{
		if ($this->current_layer > 0) {
			$this->EndLayer();
		}
		if ($id < 1) {
			return false;
		}
		if (!isset($this->layers[$id])) {
			$this->layers[$id] = ['name' => 'Layer ' . ($id)];
			if (($this->PDFA || $this->PDFX)) {
				$this->PDFAXwarnings[] = "Cannot use layers when using PDFA or PDFX";
				return '';
			} elseif (!$this->PDFA && !$this->PDFX) {
				$this->pdf_version = '1.5';
			}
		}
		$this->current_layer = $id;
		$this->writer->write('/OCZ-index /ZI' . $id . ' BDC');

		$this->pageoutput[$this->page] = [];
	}

	function EndLayer()
	{
		if ($this->current_layer > 0) {
			$this->writer->write('EMCZ-index');
			$this->current_layer = 0;
		}
	}

	function AddPageByArray($a)
	{
		if (!is_array($a)) {
			$a = [];
		}

		$orientation = (isset($a['orientation']) ? $a['orientation'] : '');
		$condition = (isset($a['condition']) ? $a['condition'] : (isset($a['type']) ? $a['type'] : ''));
		$resetpagenum = (isset($a['resetpagenum']) ? $a['resetpagenum'] : '');
		$pagenumstyle = (isset($a['pagenumstyle']) ? $a['pagenumstyle'] : '');
		$suppress = (isset($a['suppress']) ? $a['suppress'] : '');
		$mgl = (isset($a['mgl']) ? $a['mgl'] : (isset($a['margin-left']) ? $a['margin-left'] : ''));
		$mgr = (isset($a['mgr']) ? $a['mgr'] : (isset($a['margin-right']) ? $a['margin-right'] : ''));
		$mgt = (isset($a['mgt']) ? $a['mgt'] : (isset($a['margin-top']) ? $a['margin-top'] : ''));
		$mgb = (isset($a['mgb']) ? $a['mgb'] : (isset($a['margin-bottom']) ? $a['margin-bottom'] : ''));
		$mgh = (isset($a['mgh']) ? $a['mgh'] : (isset($a['margin-header']) ? $a['margin-header'] : ''));
		$mgf = (isset($a['mgf']) ? $a['mgf'] : (isset($a['margin-footer']) ? $a['margin-footer'] : ''));
		$ohname = (isset($a['ohname']) ? $a['ohname'] : (isset($a['odd-header-name']) ? $a['odd-header-name'] : ''));
		$ehname = (isset($a['ehname']) ? $a['ehname'] : (isset($a['even-header-name']) ? $a['even-header-name'] : ''));
		$ofname = (isset($a['ofname']) ? $a['ofname'] : (isset($a['odd-footer-name']) ? $a['odd-footer-name'] : ''));
		$efname = (isset($a['efname']) ? $a['efname'] : (isset($a['even-footer-name']) ? $a['even-footer-name'] : ''));
		$ohvalue = (isset($a['ohvalue']) ? $a['ohvalue'] : (isset($a['odd-header-value']) ? $a['odd-header-value'] : 0));
		$ehvalue = (isset($a['ehvalue']) ? $a['ehvalue'] : (isset($a['even-header-value']) ? $a['even-header-value'] : 0));
		$ofvalue = (isset($a['ofvalue']) ? $a['ofvalue'] : (isset($a['odd-footer-value']) ? $a['odd-footer-value'] : 0));
		$efvalue = (isset($a['efvalue']) ? $a['efvalue'] : (isset($a['even-footer-value']) ? $a['even-footer-value'] : 0));
		$pagesel = (isset($a['pagesel']) ? $a['pagesel'] : (isset($a['pageselector']) ? $a['pageselector'] : ''));
		$newformat = (isset($a['newformat']) ? $a['newformat'] : (isset($a['sheet-size']) ? $a['sheet-size'] : ''));

		$this->AddPage($orientation, $condition, $resetpagenum, $pagenumstyle, $suppress, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
	}

	// mPDF 6 pagebreaktype
	function _preForcedPagebreak($pagebreaktype)
	{
		if ($pagebreaktype == 'cloneall') {
			// Close any open block tags
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
			}
			if ($this->blklvl == 0 && !empty($this->textbuffer)) { // Output previously buffered content
				$this->printbuffer($this->textbuffer, 1);
				$this->textbuffer = [];
			}
		} elseif ($pagebreaktype == 'clonebycss') {
			// Close open block tags whilst box-decoration-break==clone
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				if (isset($this->blk[$b]['box_decoration_break']) && $this->blk[$b]['box_decoration_break'] == 'clone') {
					$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
				} else {
					if ($b == $this->blklvl && !empty($this->textbuffer)) { // Output previously buffered content
						$this->printbuffer($this->textbuffer, 1);
						$this->textbuffer = [];
					}
					break;
				}
			}
		} elseif (!empty($this->textbuffer)) { // Output previously buffered content
			$this->printbuffer($this->textbuffer, 1);
			$this->textbuffer = [];
		}
	}

	// mPDF 6 pagebreaktype
	function _postForcedPagebreak($pagebreaktype, $startpage, $save_blk, $save_blklvl)
	{
		if ($pagebreaktype == 'cloneall') {
			$this->blk = [];
			$this->blk[0] = $save_blk[0];
			// Re-open block tags
			$this->blklvl = 0;
			$arr = [];
			$i = 0;
			for ($b = 1; $b <= $save_blklvl; $b++) {
				$this->tag->OpenTag($save_blk[$b]['tag'], $save_blk[$b]['attr'], $arr, $i);
			}
		} elseif ($pagebreaktype == 'clonebycss') {
			$this->blk = [];
			$this->blk[0] = $save_blk[0];
			// Don't re-open tags for lowest level elements - so need to do some adjustments
			for ($b = 1; $b <= $this->blklvl; $b++) {
				$this->blk[$b] = $save_blk[$b];
				$this->blk[$b]['startpage'] = 0;
				$this->blk[$b]['y0'] = $this->y; // ?? $this->tMargin
				if (($this->page - $startpage) % 2) {
					if (isset($this->blk[$b]['x0'])) {
						$this->blk[$b]['x0'] += $this->MarginCorrection;
					} else {
						$this->blk[$b]['x0'] = $this->MarginCorrection;
					}
				}
				// for Float DIV
				$this->blk[$b]['marginCorrected'][$this->page] = true;
			}

			// Re-open block tags for any that have box_decoration_break==clone
			$arr = [];
			$i = 0;
			for ($b = $this->blklvl + 1; $b <= $save_blklvl; $b++) {
				if ($b < $this->blklvl) {
					$this->lastblocklevelchange = -1;
				}
				$this->tag->OpenTag($save_blk[$b]['tag'], $save_blk[$b]['attr'], $arr, $i);
			}
			if ($this->blk[$this->blklvl]['box_decoration_break'] != 'clone') {
				$this->lastblocklevelchange = -1;
			}
		} else {
			$this->lastblocklevelchange = -1;
		}
	}

	function AddPage(
		$orientation = '',
		$condition = '',
		$resetpagenum = '',
		$pagenumstyle = '',
		$suppress = '',
		$mgl = '',
		$mgr = '',
		$mgt = '',
		$mgb = '',
		$mgh = '',
		$mgf = '',
		$ohname = '',
		$ehname = '',
		$ofname = '',
		$efname = '',
		$ohvalue = 0,
		$ehvalue = 0,
		$ofvalue = 0,
		$efvalue = 0,
		$pagesel = '',
		$newformat = ''
	) {
		/* -- CSS-FLOAT -- */
		// Float DIV
		// Cannot do with columns on, or if any change in page orientation/margins etc.
		// If next page already exists - i.e background /headers and footers already written
		if ($this->state > 0 && $this->page < count($this->pages)) {
			$bak_cml = $this->cMarginL;
			$bak_cmr = $this->cMarginR;
			$bak_dw = $this->divwidth;
			// Paint Div Border if necessary
			if ($this->blklvl > 0) {
				$save_tr = $this->table_rotate; // *TABLES*
				$this->table_rotate = 0; // *TABLES*
				if (isset($this->blk[$this->blklvl]['y0']) && $this->y == $this->blk[$this->blklvl]['y0']) {
					$this->blk[$this->blklvl]['startpage'] ++;
				}
				if ((isset($this->blk[$this->blklvl]['y0']) && $this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table']) {
					$toplvl = $this->blklvl;
				} else {
					$toplvl = $this->blklvl - 1;
				}
				$sy = $this->y;
				for ($bl = 1; $bl <= $toplvl; $bl++) {
					$this->PaintDivBB('pagebottom', 0, $bl);
				}
				$this->y = $sy;
				$this->table_rotate = $save_tr; // *TABLES*
			}
			$s = $this->PrintPageBackgrounds();

			// Writes after the marker so not overwritten later by page background etc.
			$this->pages[$this->page] = preg_replace(
				'/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/',
				'\\1' . "\n" . $s . "\n",
				$this->pages[$this->page]
			);

			$this->pageBackgrounds = [];
			$family = $this->FontFamily;
			$style = $this->FontStyle;
			$size = $this->FontSizePt;
			$lw = $this->LineWidth;
			$dc = $this->DrawColor;
			$fc = $this->FillColor;
			$tc = $this->TextColor;
			$cf = $this->ColorFlag;

			$this->printfloatbuffer();

			// Move to next page
			$this->page++;

			$this->ResetMargins();
			$this->SetAutoPageBreak($this->autoPageBreak, $this->bMargin);
			$this->x = $this->lMargin;
			$this->y = $this->tMargin;
			$this->FontFamily = '';
			$this->writer->write('2 J');
			$this->LineWidth = $lw;
			$this->writer->write(sprintf('%.3F w', $lw * Mpdf::SCALE));

			if ($family) {
				$this->SetFont($family, $style, $size, true, true);
			}

			$this->DrawColor = $dc;

			if ($dc != $this->defDrawColor) {
				$this->writer->write($dc);
			}

			$this->FillColor = $fc;

			if ($fc != $this->defFillColor) {
				$this->writer->write($fc);
			}

			$this->TextColor = $tc;
			$this->ColorFlag = $cf;

			for ($bl = 1; $bl <= $this->blklvl; $bl++) {
				$this->blk[$bl]['y0'] = $this->y;
				// Don't correct more than once for background DIV containing a Float
				if (!isset($this->blk[$bl]['marginCorrected'][$this->page])) {
					if (isset($this->blk[$bl]['x0'])) {
						$this->blk[$bl]['x0'] += $this->MarginCorrection;
					} else {
						$this->blk[$bl]['x0'] = $this->MarginCorrection;
					}
				}
				$this->blk[$bl]['marginCorrected'][$this->page] = true;
			}

			$this->cMarginL = $bak_cml;
			$this->cMarginR = $bak_cmr;
			$this->divwidth = $bak_dw;

			return '';
		}
		/* -- END CSS-FLOAT -- */

		// Start a new page
		if ($this->state == 0) {
			$this->Open();
		}

		$bak_cml = $this->cMarginL;
		$bak_cmr = $this->cMarginR;
		$bak_dw = $this->divwidth;

		$bak_lh = $this->lineheight;

		$orientation = substr(strtoupper($orientation), 0, 1);
		$condition = strtoupper($condition);


		if ($condition == 'E') { // only adds new page if needed to create an Even page
			if (!$this->mirrorMargins || ($this->page) % 2 == 0) {
				return false;
			}
		} elseif ($condition == 'O') { // only adds new page if needed to create an Odd page
			if (!$this->mirrorMargins || ($this->page) % 2 == 1) {
				return false;
			}
		} elseif ($condition == 'NEXT-EVEN') { // always adds at least one new page to create an Even page
			if (!$this->mirrorMargins) {
				$condition = '';
			} else {
				if ($pagesel) {
					$pbch = $pagesel;
					$pagesel = '';
				} // *CSS-PAGE*
				else {
					$pbch = false;
				} // *CSS-PAGE*
				$this->AddPage($this->CurOrientation, 'O');
				$this->extrapagebreak = true; // mPDF 6 pagebreaktype
				if ($pbch) {
					$pagesel = $pbch;
				} // *CSS-PAGE*
				$condition = '';
			}
		} elseif ($condition == 'NEXT-ODD') { // always adds at least one new page to create an Odd page
			if (!$this->mirrorMargins) {
				$condition = '';
			} else {
				if ($pagesel) {
					$pbch = $pagesel;
					$pagesel = '';
				} // *CSS-PAGE*
				else {
					$pbch = false;
				} // *CSS-PAGE*
				$this->AddPage($this->CurOrientation, 'E');
				$this->extrapagebreak = true; // mPDF 6 pagebreaktype
				if ($pbch) {
					$pagesel = $pbch;
				} // *CSS-PAGE*
				$condition = '';
			}
		}

		if ($resetpagenum || $pagenumstyle || $suppress) {
			$this->PageNumSubstitutions[] = ['from' => ($this->page + 1), 'reset' => $resetpagenum, 'type' => $pagenumstyle, 'suppress' => $suppress];
		}

		$save_tr = $this->table_rotate; // *TABLES*
		$this->table_rotate = 0; // *TABLES*
		$save_kwt = $this->kwt;
		$this->kwt = 0;
		$save_layer = $this->current_layer;
		$save_vis = $this->visibility;

		if ($this->visibility != 'visible') {
			$this->SetVisibility('visible');
		}

		$this->EndLayer();

		// Paint Div Border if necessary
		// PAINTS BACKGROUND COLOUR OR BORDERS for DIV - DISABLED FOR COLUMNS (cf. AcceptPageBreak) AT PRESENT in ->PaintDivBB
		if (!$this->ColActive && $this->blklvl > 0) {
			if (isset($this->blk[$this->blklvl]['y0']) && $this->y == $this->blk[$this->blklvl]['y0'] && !$this->extrapagebreak) { // mPDF 6 pagebreaktype
				if (isset($this->blk[$this->blklvl]['startpage'])) {
					$this->blk[$this->blklvl]['startpage'] ++;
				} else {
					$this->blk[$this->blklvl]['startpage'] = 1;
				}
			}
			if ((isset($this->blk[$this->blklvl]['y0']) && $this->y > $this->blk[$this->blklvl]['y0']) || $this->flowingBlockAttr['is_table'] || $this->extrapagebreak) {
				$toplvl = $this->blklvl;
			} // mPDF 6 pagebreaktype
			else {
				$toplvl = $this->blklvl - 1;
			}
			$sy = $this->y;
			for ($bl = 1; $bl <= $toplvl; $bl++) {
				if (isset($this->blk[$bl]['z-index']) && $this->blk[$bl]['z-index'] > 0) {
					$this->BeginLayer($this->blk[$bl]['z-index']);
				}
				if (isset($this->blk[$bl]['visibility']) && $this->blk[$bl]['visibility'] && $this->blk[$bl]['visibility'] != 'visible') {
					$this->SetVisibility($this->blk[$bl]['visibility']);
				}
				$this->PaintDivBB('pagebottom', 0, $bl);
			}
			$this->y = $sy;
			// RESET block y0 and x0 - see below
		}
		$this->extrapagebreak = false; // mPDF 6 pagebreaktype

		if ($this->visibility != 'visible') {
			$this->SetVisibility('visible');
		}

		$this->EndLayer();

		// BODY Backgrounds
		if ($this->page > 0) {
			$s = '';
			$s .= $this->PrintBodyBackgrounds();

			$s .= $this->PrintPageBackgrounds();
			$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/', "\n" . $s . "\n" . '\\1', $this->pages[$this->page]);
			$this->pageBackgrounds = [];
		}

		$save_kt = $this->keep_block_together;
		$this->keep_block_together = 0;

		$save_cols = false;

		/* -- COLUMNS -- */
		if ($this->ColActive) {
			$save_cols = true;
			$save_nbcol = $this->NbCol; // other values of gap and vAlign will not change by setting Columns off
			$this->SetColumns(0);
		}
		/* -- END COLUMNS -- */

		$family = $this->FontFamily;
		$style = $this->FontStyle;
		$size = $this->FontSizePt;
		$this->ColumnAdjust = true; // enables column height adjustment for the page
		$lw = $this->LineWidth;
		$dc = $this->DrawColor;
		$fc = $this->FillColor;
		$tc = $this->TextColor;
		$cf = $this->ColorFlag;
		if ($this->page > 0) {
			// Page footer
			$this->InFooter = true;

			$this->Reset();
			$this->pageoutput[$this->page] = [];

			$this->Footer();
			// Close page
			$this->_endpage();
		}

		// Start new page
		$pageBeforeNewPage = $this->page;
		$this->_beginpage($orientation, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $ohname, $ehname, $ofname, $efname, $ohvalue, $ehvalue, $ofvalue, $efvalue, $pagesel, $newformat);
		$isNewPage = $pageBeforeNewPage !== $this->page;

		if ($this->docTemplate) {
			$currentReaderId = $this->currentReaderId;

			$pagecount = $this->setSourceFile($this->docTemplate);
			if (($this->page - $this->docTemplateStart) > $pagecount) {
				if ($this->docTemplateContinue) {
					if ($this->docTemplateContinue2pages && $pagecount >= 2 && (0 === $this->page % 2)) {
						$tplIdx = $this->importPage(($pagecount - 1));
						$this->useTemplate($tplIdx);
					} else {
						$tplIdx = $this->importPage($pagecount);
						$this->useTemplate($tplIdx);
					}
				}
			} else {
				$tplIdx = $this->importPage(($this->page - $this->docTemplateStart));
				$this->useTemplate($tplIdx);
			}

			$this->currentReaderId = $currentReaderId;
		}

		if ($this->pageTemplate) {
			$this->useTemplate($this->pageTemplate);
		}

		// Only add the headers if it's a new page
		if ($isNewPage) {
			// Tiling Patterns
			$this->writer->write('___PAGE___START' . $this->uniqstr);
			$this->writer->write('___BACKGROUND___PATTERNS' . $this->uniqstr);
			$this->writer->write('___HEADER___MARKER' . $this->uniqstr);
		}

		$this->pageBackgrounds = [];

		// Set line cap style to square
		$this->SetLineCap(2);
		// Set line width
		$this->LineWidth = $lw;
		$this->writer->write(sprintf('%.3F w', $lw * Mpdf::SCALE));
		// Set font
		if ($family) {
			$this->SetFont($family, $style, $size, true, true); // forces write
		}

		// Set colors
		$this->DrawColor = $dc;
		if ($dc != $this->defDrawColor) {
			$this->writer->write($dc);
		}
		$this->FillColor = $fc;
		if ($fc != $this->defFillColor) {
			$this->writer->write($fc);
		}
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;

		// Page header
		$this->Header();

		// Restore line width
		if ($this->LineWidth != $lw) {
			$this->LineWidth = $lw;
			$this->writer->write(sprintf('%.3F w', $lw * Mpdf::SCALE));
		}
		// Restore font
		if ($family) {
			$this->SetFont($family, $style, $size, true, true); // forces write
		}

		// Restore colors
		if ($this->DrawColor != $dc) {
			$this->DrawColor = $dc;
			$this->writer->write($dc);
		}
		if ($this->FillColor != $fc) {
			$this->FillColor = $fc;
			$this->writer->write($fc);
		}
		$this->TextColor = $tc;
		$this->ColorFlag = $cf;
		$this->InFooter = false;

		if ($save_layer > 0) {
			$this->BeginLayer($save_layer);
		}

		if ($save_vis != 'visible') {
			$this->SetVisibility($save_vis);
		}

		/* -- COLUMNS -- */
		if ($save_cols) {
			// Restore columns
			$this->SetColumns($save_nbcol, $this->colvAlign, $this->ColGap);
		}
		if ($this->ColActive) {
			$this->SetCol(0);
		}
		/* -- END COLUMNS -- */


		// RESET BLOCK BORDER TOP
		if (!$this->ColActive) {
			for ($bl = 1; $bl <= $this->blklvl; $bl++) {
				$this->blk[$bl]['y0'] = $this->y;
				if (isset($this->blk[$bl]['x0'])) {
					$this->blk[$bl]['x0'] += $this->MarginCorrection;
				} else {
					$this->blk[$bl]['x0'] = $this->MarginCorrection;
				}
				// Added mPDF 3.0 Float DIV
				$this->blk[$bl]['marginCorrected'][$this->page] = true;
			}
		}


		$this->table_rotate = $save_tr; // *TABLES*
		$this->kwt = $save_kwt;

		$this->keep_block_together = $save_kt;

		$this->cMarginL = $bak_cml;
		$this->cMarginR = $bak_cmr;
		$this->divwidth = $bak_dw;

		$this->lineheight = $bak_lh;
	}

	/**
	 * Get current page number
	 *
	 * @return int
	 */
	function PageNo()
	{
		return $this->page;
	}

	function AddSpotColorsFromFile($file)
	{
		$colors = @file($file);
		if (!$colors) {
			throw new \Mpdf\MpdfException("Cannot load spot colors file - " . $file);
		}
		foreach ($colors as $sc) {
			list($name, $c, $m, $y, $k) = preg_split("/\t/", $sc);
			$c = intval($c);
			$m = intval($m);
			$y = intval($y);
			$k = intval($k);
			$this->AddSpotColor($name, $c, $m, $y, $k);
		}
	}

	function AddSpotColor($name, $c, $m, $y, $k)
	{
		$name = strtoupper(trim($name));
		if (!isset($this->spotColors[$name])) {
			$i = count($this->spotColors) + 1;
			$this->spotColors[$name] = ['i' => $i, 'c' => $c, 'm' => $m, 'y' => $y, 'k' => $k];
			$this->spotColorIDs[$i] = $name;
		}
	}

	function SetColor($col, $type = '')
	{
		$out = '';
		if (!$col) {
			return '';
		} // mPDF 6
		if ($col[0] == 3 || $col[0] == 5) { // RGB / RGBa
			$out = sprintf('%.3F %.3F %.3F rg', ord($col[1]) / 255, ord($col[2]) / 255, ord($col[3]) / 255);
		} elseif ($col[0] == 1) { // GRAYSCALE
			$out = sprintf('%.3F g', ord($col[1]) / 255);
		} elseif ($col[0] == 2) { // SPOT COLOR
			$out = sprintf('/CS%d cs %.3F scn', ord($col[1]), ord($col[2]) / 100);
		} elseif ($col[0] == 4 || $col[0] == 6) { // CMYK / CMYKa
			$out = sprintf('%.3F %.3F %.3F %.3F k', ord($col[1]) / 100, ord($col[2]) / 100, ord($col[3]) / 100, ord($col[4]) / 100);
		}
		if ($type == 'Draw') {
			$out = strtoupper($out);
		} // e.g. rg => RG
		elseif ($type == 'CodeOnly') {
			$out = preg_replace('/\s(rg|g|k)/', '', $out);
		}
		return $out;
	}

	function SetDColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Draw');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->DrawColor = $out;
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['DrawColor']) && $this->pageoutput[$this->page]['DrawColor'] != $this->DrawColor) || !isset($this->pageoutput[$this->page]['DrawColor']))) {
			$this->writer->write($this->DrawColor);
		}
		$this->pageoutput[$this->page]['DrawColor'] = $this->DrawColor;
	}

	function SetFColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Fill');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->FillColor = $out;
		$this->ColorFlag = ($out != $this->TextColor);
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['FillColor']) && $this->pageoutput[$this->page]['FillColor'] != $this->FillColor) || !isset($this->pageoutput[$this->page]['FillColor']))) {
			$this->writer->write($this->FillColor);
		}
		$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
	}

	function SetTColor($col, $return = false)
	{
		$out = $this->SetColor($col, 'Text');
		if ($return) {
			return $out;
		}
		if ($out == '') {
			return '';
		}
		$this->TextColor = $out;
		$this->ColorFlag = ($this->FillColor != $out);
	}

	function SetDrawColor($r, $g = -1, $b = -1, $col4 = -1, $return = false)
	{
		// Set color for all stroking operations
		$col = [];
		if (($r == 0 and $g == 0 and $b == 0 && $col4 == -1) or $g == -1) {
			$col = $this->colorConverter->convert($r, $this->PDFAXwarnings);
		} elseif ($col4 == -1) {
			$col = $this->colorConverter->convert('rgb(' . $r . ',' . $g . ',' . $b . ')', $this->PDFAXwarnings);
		} else {
			$col = $this->colorConverter->convert('cmyk(' . $r . ',' . $g . ',' . $b . ',' . $col4 . ')', $this->PDFAXwarnings);
		}
		$out = $this->SetDColor($col, $return);
		return $out;
	}

	function SetFillColor($r, $g = -1, $b = -1, $col4 = -1, $return = false)
	{
		// Set color for all filling operations
		$col = [];
		if (($r == 0 and $g == 0 and $b == 0 && $col4 == -1) or $g == -1) {
			$col = $this->colorConverter->convert($r, $this->PDFAXwarnings);
		} elseif ($col4 == -1) {
			$col = $this->colorConverter->convert('rgb(' . $r . ',' . $g . ',' . $b . ')', $this->PDFAXwarnings);
		} else {
			$col = $this->colorConverter->convert('cmyk(' . $r . ',' . $g . ',' . $b . ',' . $col4 . ')', $this->PDFAXwarnings);
		}
		$out = $this->SetFColor($col, $return);
		return $out;
	}

	function SetTextColor($r, $g = -1, $b = -1, $col4 = -1, $return = false)
	{
		// Set color for text
		$col = [];
		if (($r == 0 and $g == 0 and $b == 0 && $col4 == -1) or $g == -1) {
			$col = $this->colorConverter->convert($r, $this->PDFAXwarnings);
		} elseif ($col4 == -1) {
			$col = $this->colorConverter->convert('rgb(' . $r . ',' . $g . ',' . $b . ')', $this->PDFAXwarnings);
		} else {
			$col = $this->colorConverter->convert('cmyk(' . $r . ',' . $g . ',' . $b . ',' . $col4 . ')', $this->PDFAXwarnings);
		}
		$out = $this->SetTColor($col, $return);
		return $out;
	}

	function _getCharWidth(&$cw, $u, $isdef = true)
	{
		$w = 0;

		if ($u == 0) {
			$w = false;
		} elseif (isset($cw[$u * 2 + 1])) {
			$w = (ord($cw[$u * 2]) << 8) + ord($cw[$u * 2 + 1]);
		}

		if ($w == 65535) {
			return 0;
		} elseif ($w) {
			return $w;
		} elseif ($isdef) {
			return false;
		} else {
			return 0;
		}
	}

	function _charDefined(&$cw, $u)
	{
		$w = 0;
		if ($u == 0) {
			return false;
		}
		if (isset($cw[$u * 2 + 1])) {
			$w = (ord($cw[$u * 2]) << 8) + ord($cw[$u * 2 + 1]);
		}

		return (bool) $w;
	}

	function GetCharWidthCore($c)
	{
		// Get width of a single character in the current Core font
		$c = (string) $c;
		$w = 0;
		// Soft Hyphens chr(173)
		if ($c == chr(173) && $this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
			return 0;
		} elseif (($this->textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[ord($c)])) {  // mPDF 5.7.1
			$charw = $this->CurrentFont['cw'][chr($this->upperCase[ord($c)])];
			if ($charw !== false) {
				$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
				$w+=$charw;
			}
		} elseif (isset($this->CurrentFont['cw'][$c])) {
			$w += $this->CurrentFont['cw'][$c];
		} elseif (isset($this->CurrentFont['cw'][ord($c)])) {
			$w += $this->CurrentFont['cw'][ord($c)];
		}
		$w *= ($this->FontSize / 1000);
		if ($this->minwSpacing || $this->fixedlSpacing) {
			if ($c == ' ') {
				$nb_spaces = 1;
			} else {
				$nb_spaces = 0;
			}
			$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
		}
		return ($w);
	}

	function GetCharWidthNonCore($c, $addSubset = true)
	{
		// Get width of a single character in the current Non-Core font
		$c = (string) $c;
		$w = 0;
		$unicode = $this->UTF8StringToArray($c, $addSubset);
		$char = $unicode[0];
		/* -- CJK-FONTS -- */
		if ($this->CurrentFont['type'] == 'Type0') { // CJK Adobe fonts
			if ($char == 173) {
				return 0;
			} // Soft Hyphens
			elseif (isset($this->CurrentFont['cw'][$char])) {
				$w+=$this->CurrentFont['cw'][$char];
			} elseif (isset($this->CurrentFont['MissingWidth'])) {
				$w += $this->CurrentFont['MissingWidth'];
			} else {
				$w += 500;
			}
		} else {
			/* -- END CJK-FONTS -- */
			if ($char == 173) {
				return 0;
			} // Soft Hyphens
			elseif (($this->textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[$char])) { // mPDF 5.7.1
				$charw = $this->_getCharWidth($this->CurrentFont['cw'], $this->upperCase[$char]);
				if ($charw !== false) {
					$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
					$w+=$charw;
				} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$w += $this->CurrentFont['desc']['MissingWidth'];
				} elseif (isset($this->CurrentFont['MissingWidth'])) {
					$w += $this->CurrentFont['MissingWidth'];
				} else {
					$w += 500;
				}
			} else {
				$charw = $this->_getCharWidth($this->CurrentFont['cw'], $char);
				if ($charw !== false) {
					$w+=$charw;
				} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
					$w += $this->CurrentFont['desc']['MissingWidth'];
				} elseif (isset($this->CurrentFont['MissingWidth'])) {
					$w += $this->CurrentFont['MissingWidth'];
				} else {
					$w += 500;
				}
			}
		} // *CJK-FONTS*
		$w *= ($this->FontSize / 1000);
		if ($this->minwSpacing || $this->fixedlSpacing) {
			if ($c == ' ') {
				$nb_spaces = 1;
			} else {
				$nb_spaces = 0;
			}
			$w += $this->fixedlSpacing + ($nb_spaces * $this->minwSpacing);
		}
		return ($w);
	}

	function GetCharWidth($c, $addSubset = true)
	{
		if (!$this->usingCoreFont) {
			return $this->GetCharWidthNonCore($c, $addSubset);
		} else {
			return $this->GetCharWidthCore($c);
		}
	}

	function GetStringWidth($s, $addSubset = true, $OTLdata = false, $textvar = 0, $includeKashida = false)
	{
	// mPDF 5.7.1
		// Get width of a string in the current font
		$s = (string) $s;
		$cw = &$this->CurrentFont['cw'];
		$w = 0;
		$kerning = 0;
		$lastchar = 0;
		$nb_carac = 0;
		$nb_spaces = 0;
		$kashida = 0;
		// mPDF ITERATION
		if ($this->iterationCounter) {
			$s = preg_replace('/{iteration ([a-zA-Z0-9_]+)}/', '\\1', $s);
		}
		if (!$this->usingCoreFont) {
			$discards = substr_count($s, "\xc2\xad"); // mPDF 6 soft hyphens [U+00AD]
			$unicode = $this->UTF8StringToArray($s, $addSubset);
			if ($this->minwSpacing || $this->fixedlSpacing) {
				$nb_spaces = mb_substr_count($s, ' ', $this->mb_enc);
				$nb_carac = count($unicode) - $discards; // mPDF 6
				// mPDF 5.7.1
				// Use GPOS OTL
				if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
					if (isset($OTLdata['group']) && $OTLdata['group']) {
						$nb_carac -= substr_count($OTLdata['group'], 'M');
					}
				}
			}
			/* -- CJK-FONTS -- */
			if ($this->CurrentFont['type'] == 'Type0') { // CJK Adobe fonts
				foreach ($unicode as $char) {
					if ($char == 0x00AD) {
						continue;
					} // mPDF 6 soft hyphens [U+00AD]
					if (isset($cw[$char])) {
						$w+=$cw[$char];
					} elseif (isset($this->CurrentFont['MissingWidth'])) {
						$w += $this->CurrentFont['MissingWidth'];
					} else {
						$w += 500;
					}
				}
			} else {
				/* -- END CJK-FONTS -- */
				foreach ($unicode as $i => $char) {
					if ($char == 0x00AD) {
						continue;
					} // mPDF 6 soft hyphens [U+00AD]
					if (($textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[$char])) {
						$charw = $this->_getCharWidth($cw, $this->upperCase[$char]);
						if ($charw !== false) {
							$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
							$w+=$charw;
						} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
							$w += $this->CurrentFont['desc']['MissingWidth'];
						} elseif (isset($this->CurrentFont['MissingWidth'])) {
							$w += $this->CurrentFont['MissingWidth'];
						} else {
							$w += 500;
						}
					} else {
						$charw = $this->_getCharWidth($cw, $char);
						if ($charw !== false) {
							$w+=$charw;
						} elseif (isset($this->CurrentFont['desc']['MissingWidth'])) {
							$w += $this->CurrentFont['desc']['MissingWidth'];
						} elseif (isset($this->CurrentFont['MissingWidth'])) {
							$w += $this->CurrentFont['MissingWidth'];
						} else {
							$w += 500;
						}
						// mPDF 5.7.1
						// Use GPOS OTL
						// ...GetStringWidth...
						if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && !empty($OTLdata)) {
							if (isset($OTLdata['GPOSinfo'][$i]['wDir']) && $OTLdata['GPOSinfo'][$i]['wDir'] == 'RTL') {
								if (isset($OTLdata['GPOSinfo'][$i]['XAdvanceR']) && $OTLdata['GPOSinfo'][$i]['XAdvanceR']) {
									$w += $OTLdata['GPOSinfo'][$i]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
								}
							} else {
								if (isset($OTLdata['GPOSinfo'][$i]['XAdvanceL']) && $OTLdata['GPOSinfo'][$i]['XAdvanceL']) {
									$w += $OTLdata['GPOSinfo'][$i]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
								}
							}
							// Kashida from GPOS
							// Kashida is set as an absolute length value (already set as a proportion based on useKashida %)
							if ($includeKashida && isset($OTLdata['GPOSinfo'][$i]['kashida_space']) && $OTLdata['GPOSinfo'][$i]['kashida_space']) {
								$kashida += $OTLdata['GPOSinfo'][$i]['kashida_space'];
							}
						}
						if (($textvar & TextVars::FC_KERNING) && $lastchar) {
							if (isset($this->CurrentFont['kerninfo'][$lastchar][$char])) {
								$kerning += $this->CurrentFont['kerninfo'][$lastchar][$char];
							}
						}
						$lastchar = $char;
					}
				}
			} // *CJK-FONTS*
		} else {
			if ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
				$s = str_replace(chr(173), '', $s);
			}
			$nb_carac = $l = strlen($s);
			if ($this->minwSpacing || $this->fixedlSpacing) {
				$nb_spaces = substr_count($s, ' ');
			}
			for ($i = 0; $i < $l; $i++) {
				if (($textvar & TextVars::FC_SMALLCAPS) && isset($this->upperCase[ord($s[$i])])) {  // mPDF 5.7.1
					$charw = $cw[chr($this->upperCase[ord($s[$i])])];
					if ($charw !== false) {
						$charw = $charw * $this->smCapsScale * $this->smCapsStretch / 100;
						$w+=$charw;
					}
				} elseif (isset($cw[$s[$i]])) {
					$w += $cw[$s[$i]];
				} elseif (isset($cw[ord($s[$i])])) {
					$w += $cw[ord($s[$i])];
				}
				if (($textvar & TextVars::FC_KERNING) && $i > 0) { // mPDF 5.7.1
					if (isset($this->CurrentFont['kerninfo'][$s[($i - 1)]][$s[$i]])) {
						$kerning += $this->CurrentFont['kerninfo'][$s[($i - 1)]][$s[$i]];
					}
				}
			}
		}
		unset($cw);
		if ($textvar & TextVars::FC_KERNING) {
			$w += $kerning;
		} // mPDF 5.7.1
		$w *= ($this->FontSize / 1000);
		$w += (($nb_carac + $nb_spaces) * $this->fixedlSpacing) + ($nb_spaces * $this->minwSpacing);
		$w += $kashida / Mpdf::SCALE;

		return ($w);
	}

	function SetLineWidth($width)
	{
		// Set line width
		$this->LineWidth = $width;
		$lwout = (sprintf('%.3F w', $width * Mpdf::SCALE));
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['LineWidth']) && $this->pageoutput[$this->page]['LineWidth'] != $lwout) || !isset($this->pageoutput[$this->page]['LineWidth']))) {
			$this->writer->write($lwout);
		}
		$this->pageoutput[$this->page]['LineWidth'] = $lwout;
	}

	function Line($x1, $y1, $x2, $y2)
	{
		// Draw a line
		$this->writer->write(sprintf('%.3F %.3F m %.3F %.3F l S', $x1 * Mpdf::SCALE, ($this->h - $y1) * Mpdf::SCALE, $x2 * Mpdf::SCALE, ($this->h - $y2) * Mpdf::SCALE));
	}

	function Arrow($x1, $y1, $x2, $y2, $headsize = 3, $fill = 'B', $angle = 25)
	{
		// F == fill // S == stroke // B == stroke and fill
		// angle = splay of arrowhead - 1 - 89 degrees
		if ($fill == 'F') {
			$fill = 'f';
		} elseif ($fill == 'FD' or $fill == 'DF' or $fill == 'B') {
			$fill = 'B';
		} else {
			$fill = 'S';
		}
		$a = atan2(($y2 - $y1), ($x2 - $x1));
		$b = $a + deg2rad($angle);
		$c = $a - deg2rad($angle);
		$x3 = $x2 - ($headsize * cos($b));
		$y3 = $this->h - ($y2 - ($headsize * sin($b)));
		$x4 = $x2 - ($headsize * cos($c));
		$y4 = $this->h - ($y2 - ($headsize * sin($c)));

		$x5 = $x3 - ($x3 - $x4) / 2; // mid point of base of arrowhead - to join arrow line to
		$y5 = $y3 - ($y3 - $y4) / 2;

		$s = '';
		$s .= sprintf('%.3F %.3F m %.3F %.3F l S', $x1 * Mpdf::SCALE, ($this->h - $y1) * Mpdf::SCALE, $x5 * Mpdf::SCALE, $y5 * Mpdf::SCALE);
		$this->writer->write($s);

		$s = '';
		$s .= sprintf('%.3F %.3F m %.3F %.3F l %.3F %.3F l %.3F %.3F l %.3F %.3F l ', $x5 * Mpdf::SCALE, $y5 * Mpdf::SCALE, $x3 * Mpdf::SCALE, $y3 * Mpdf::SCALE, $x2 * Mpdf::SCALE, ($this->h - $y2) * Mpdf::SCALE, $x4 * Mpdf::SCALE, $y4 * Mpdf::SCALE, $x5 * Mpdf::SCALE, $y5 * Mpdf::SCALE);
		$s .= $fill;
		$this->writer->write($s);
	}

	function Rect($x, $y, $w, $h, $style = '')
	{
		// Draw a rectangle
		if ($style == 'F') {
			$op = 'f';
		} elseif ($style == 'FD' or $style == 'DF') {
			$op = 'B';
		} else {
			$op = 'S';
		}
		$this->writer->write(sprintf('%.3F %.3F %.3F %.3F re %s', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $w * Mpdf::SCALE, -$h * Mpdf::SCALE, $op));
	}

	function AddFontDirectory($directory)
	{
		$this->fontDir[] = $directory;
		$this->fontFileFinder->setDirectories($this->fontDir);
	}

	function AddFont($family, $style = '')
	{
		if (empty($family)) {
			return;
		}

		$family = strtolower($family);
		$style = strtoupper($style);
		$style = str_replace('U', '', $style);

		if ($style == 'IB') {
			$style = 'BI';
		}

		$fontkey = $family . $style;

		// check if the font has been already added
		if (isset($this->fonts[$fontkey])) {
			return;
		}

		/* -- CJK-FONTS -- */
		if (in_array($family, $this->available_CJK_fonts)) {
			if (empty($this->Big5_widths)) {
				require __DIR__ . '/../data/CJKdata.php';
			}
			$this->AddCJKFont($family); // don't need to add style
			return;
		}
		/* -- END CJK-FONTS -- */

		if ($this->usingCoreFont) {
			throw new \Mpdf\MpdfException("mPDF Error - problem with Font management");
		}

		$stylekey = $style;
		if (!$style) {
			$stylekey = 'R';
		}

		if (!isset($this->fontdata[$family][$stylekey]) || !$this->fontdata[$family][$stylekey]) {
			throw new \Mpdf\MpdfException(sprintf('Font "%s%s%s" is not supported', $family, $style ? ' - ' : '', $style));
		}

		/* Setup defaults */
		$font = [
			'name' => '',
			'type' => '',
			'desc' => '',
			'panose' => '',
			'unitsPerEm' => '',
			'up' => '',
			'ut' => '',
			'strs' => '',
			'strp' => '',
			'sip' => false,
			'smp' => false,
			'useOTL' => 0,
			'fontmetrics' => '',
			'haskerninfo' => false,
			'haskernGPOS' => false,
			'hassmallcapsGSUB' => false,
			'BMPselected' => false,
			'GSUBScriptLang' => [],
			'GSUBFeatures' => [],
			'GSUBLookups' => [],
			'GPOSScriptLang' => [],
			'GPOSFeatures' => [],
			'GPOSLookups' => [],
			'rtlPUAstr' => '',
		];

		$fontCacheFilename = $fontkey . '.mtx.json';
		if ($this->fontCache->jsonHas($fontCacheFilename)) {
			$font = $this->fontCache->jsonLoad($fontCacheFilename);
		}

		$ttffile = $this->fontFileFinder->findFontFile($this->fontdata[$family][$stylekey]);
		$ttfstat = stat($ttffile);

		$TTCfontID = isset($this->fontdata[$family]['TTCfontID'][$stylekey]) ? isset($this->fontdata[$family]['TTCfontID'][$stylekey]) : 0;
		$fontUseOTL = isset($this->fontdata[$family]['useOTL']) ? $this->fontdata[$family]['useOTL'] : false;
		$BMPonly = in_array($family, $this->BMPonly) ? true : false;

		$regenerate = false;
		if ($BMPonly && !$font['BMPselected']) {
			$regenerate = true;
		} elseif (!$BMPonly && $font['BMPselected']) {
			$regenerate = true;
		}

		if ($fontUseOTL && $font['useOTL'] != $fontUseOTL) {
			$regenerate = true;
			$font['useOTL'] = $fontUseOTL;
		} elseif (!$fontUseOTL && $font['useOTL']) {
			$regenerate = true;
			$font['useOTL'] = 0;
		}

		if ($this->fontDescriptor != $font['fontmetrics']) {
			$regenerate = true;
		} // mPDF 6

		if (empty($font['name']) || $font['originalsize'] != $ttfstat['size'] || $regenerate) {
			$generator = new MetricsGenerator($this->fontCache, $this->fontDescriptor);

			$generator->generateMetrics(
				$ttffile,
				$ttfstat,
				$fontkey,
				$TTCfontID,
				$this->debugfonts,
				$BMPonly,
				$font['useOTL'],
				$fontUseOTL
			);

			$font = $this->fontCache->jsonLoad($fontCacheFilename);
			$cw = $this->fontCache->load($fontkey . '.cw.dat');
			$glyphIDtoUni = $this->fontCache->load($fontkey . '.gid.dat');
		} else {
			if ($this->fontCache->has($fontkey . '.cw.dat')) {
				$cw = $this->fontCache->load($fontkey . '.cw.dat');
			}

			if ($this->fontCache->has($fontkey . '.gid.dat')) {
				$glyphIDtoUni = $this->fontCache->load($fontkey . '.gid.dat');
			}
		}

		if (isset($this->fontdata[$family]['sip-ext']) && $this->fontdata[$family]['sip-ext']) {
			$sipext = $this->fontdata[$family]['sip-ext'];
		} else {
			$sipext = '';
		}

		// Override with values from config_font.php
		if (isset($this->fontdata[$family]['Ascent']) && $this->fontdata[$family]['Ascent']) {
			$desc['Ascent'] = $this->fontdata[$family]['Ascent'];
		}
		if (isset($this->fontdata[$family]['Descent']) && $this->fontdata[$family]['Descent']) {
			$desc['Descent'] = $this->fontdata[$family]['Descent'];
		}
		if (isset($this->fontdata[$family]['Leading']) && $this->fontdata[$family]['Leading']) {
			$desc['Leading'] = $this->fontdata[$family]['Leading'];
		}

		$i = count($this->fonts) + $this->extraFontSubsets + 1;

		$this->fonts[$fontkey] = [
			'i' => $i,
			'name' => $font['name'],
			'type' => $font['type'],
			'desc' => $font['desc'],
			'panose' => $font['panose'],
			'unitsPerEm' => $font['unitsPerEm'],
			'up' => $font['up'],
			'ut' => $font['ut'],
			'strs' => $font['strs'],
			'strp' => $font['strp'],
			'cw' => $cw,
			'ttffile' => $ttffile,
			'fontkey' => $fontkey,
			'used' => false,
			'sip' => $font['sip'],
			'sipext' => $sipext,
			'smp' => $font['smp'],
			'TTCfontID' => $TTCfontID,
			'useOTL' => $fontUseOTL,
			'useKashida' => (isset($this->fontdata[$family]['useKashida']) ? $this->fontdata[$family]['useKashida'] : false),
			'GSUBScriptLang' => $font['GSUBScriptLang'],
			'GSUBFeatures' => $font['GSUBFeatures'],
			'GSUBLookups' => $font['GSUBLookups'],
			'GPOSScriptLang' => $font['GPOSScriptLang'],
			'GPOSFeatures' => $font['GPOSFeatures'],
			'GPOSLookups' => $font['GPOSLookups'],
			'rtlPUAstr' => $font['rtlPUAstr'],
			'glyphIDtoUni' => $glyphIDtoUni,
			'haskerninfo' => $font['haskerninfo'],
			'haskernGPOS' => $font['haskernGPOS'],
			'hassmallcapsGSUB' => $font['hassmallcapsGSUB'],
		];


		if (!$font['sip'] && !$font['smp']) {
			$subsetRange = range(32, 127);
			$this->fonts[$fontkey]['subset'] = array_combine($subsetRange, $subsetRange);
		} else {
			$this->fonts[$fontkey]['subsets'] = [0 => range(0, 127)];
			$this->fonts[$fontkey]['subsetfontids'] = [$i];
		}

		if ($font['haskerninfo']) {
			$this->fonts[$fontkey]['kerninfo'] = $font['kerninfo'];
		}

		$this->FontFiles[$fontkey] = [
			'length1' => $font['originalsize'],
			'type' => 'TTF',
			'ttffile' => $ttffile,
			'sip' => $font['sip'],
			'smp' => $font['smp'],
		];

		unset($cw);
	}

	function SetFont($family, $style = '', $size = 0, $write = true, $forcewrite = false)
	{
		$family = strtolower($family);

		if (!$this->onlyCoreFonts) {
			if ($family == 'sans' || $family == 'sans-serif') {
				$family = $this->sans_fonts[0];
			}
			if ($family == 'serif') {
				$family = $this->serif_fonts[0];
			}
			if ($family == 'mono' || $family == 'monospace') {
				$family = $this->mono_fonts[0];
			}
		}

		if (isset($this->fonttrans[$family]) && $this->fonttrans[$family]) {
			$family = $this->fonttrans[$family];
		}

		if ($family == '') {
			if ($this->FontFamily) {
				$family = $this->FontFamily;
			} elseif ($this->default_font) {
				$family = $this->default_font;
			} else {
				throw new \Mpdf\MpdfException("No font or default font set!");
			}
		}

		$this->ReqFontStyle = $style; // required or requested style - used later for artificial bold/italic

		if (($family == 'csymbol') || ($family == 'czapfdingbats') || ($family == 'ctimes') || ($family == 'ccourier') || ($family == 'chelvetica')) {
			if ($this->PDFA || $this->PDFX) {
				if ($family == 'csymbol' || $family == 'czapfdingbats') {
					throw new \Mpdf\MpdfException("Symbol and Zapfdingbats cannot be embedded in mPDF (required for PDFA1-b or PDFX/1-a).");
				}
				if ($family == 'ctimes' || $family == 'ccourier' || $family == 'chelvetica') {
					if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) {
						$this->PDFAXwarnings[] = "Core Adobe font " . ucfirst($family) . " cannot be embedded in mPDF, which is required for PDFA1-b or PDFX/1-a. (Embedded font will be substituted.)";
					}
					if ($family == 'chelvetica') {
						$family = 'sans';
					}
					if ($family == 'ctimes') {
						$family = 'serif';
					}
					if ($family == 'ccourier') {
						$family = 'mono';
					}
				}
				$this->usingCoreFont = false;
			} else {
				$this->usingCoreFont = true;
			}
			if ($family == 'csymbol' || $family == 'czapfdingbats') {
				$style = '';
			}
		} else {
			$this->usingCoreFont = false;
		}

		// mPDF 5.7.1
		if ($style) {
			$style = strtoupper($style);
			if ($style == 'IB') {
				$style = 'BI';
			}
		}

		if (!$size) {
			$size = $this->FontSizePt;
		}

		$fontkey = $family . $style;

		$stylekey = $style;

		if (!$stylekey) {
			$stylekey = "R";
		}

		if (!$this->onlyCoreFonts && !$this->usingCoreFont) {
			if (!isset($this->fonts[$fontkey]) || count($this->default_available_fonts) != count($this->available_unifonts)) { // not already added

				/* -- CJK-FONTS -- */
				if (in_array($fontkey, $this->available_CJK_fonts)) {
					if (!isset($this->fonts[$fontkey])) { // already added
						if (empty($this->Big5_widths)) {
							require __DIR__ . '/../data/CJKdata.php';
						}
						$this->AddCJKFont($family); // don't need to add style
					}
				} else { // Test to see if requested font/style is available - or substitute /* -- END CJK-FONTS -- */
					if (!in_array($fontkey, $this->available_unifonts)) {
						// If font[nostyle] exists - set it
						if (in_array($family, $this->available_unifonts)) {
							$style = '';
						} // elseif only one font available - set it (assumes if only one font available it will not have a style)
						elseif (count($this->available_unifonts) == 1) {
							$family = $this->available_unifonts[0];
							$style = '';
						} else {
							$found = 0;
							// else substitute font of similar type
							if (in_array($family, $this->sans_fonts)) {
								$i = array_intersect($this->sans_fonts, $this->available_unifonts);
								if (count($i)) {
									$i = array_values($i);
									// with requested style if possible
									if (!in_array(($i[0] . $style), $this->available_unifonts)) {
										$style = '';
									}
									$family = $i[0];
									$found = 1;
								}
							} elseif (in_array($family, $this->serif_fonts)) {
								$i = array_intersect($this->serif_fonts, $this->available_unifonts);
								if (count($i)) {
									$i = array_values($i);
									// with requested style if possible
									if (!in_array(($i[0] . $style), $this->available_unifonts)) {
										$style = '';
									}
									$family = $i[0];
									$found = 1;
								}
							} elseif (in_array($family, $this->mono_fonts)) {
								$i = array_intersect($this->mono_fonts, $this->available_unifonts);
								if (count($i)) {
									$i = array_values($i);
									// with requested style if possible
									if (!in_array(($i[0] . $style), $this->available_unifonts)) {
										$style = '';
									}
									$family = $i[0];
									$found = 1;
								}
							}

							if (!$found) {
								// set first available font
								$fs = $this->available_unifonts[0];
								preg_match('/^([a-z_0-9\-]+)([BI]{0,2})$/', $fs, $fas); // Allow "-"
								// with requested style if possible
								$ws = $fas[1] . $style;
								if (in_array($ws, $this->available_unifonts)) {
									$family = $fas[1]; // leave $style as is
								} elseif (in_array($fas[1], $this->available_unifonts)) {
									// or without style
									$family = $fas[1];
									$style = '';
								} else {
									// or with the style specified
									$family = $fas[1];
									$style = $fas[2];
								}
							}
						}
						$fontkey = $family . $style;
					}
				}
			}

			// try to add font (if not already added)
			$this->AddFont($family, $style);

			// Test if font is already selected
			if ($this->FontFamily == $family && $this->FontFamily == $this->currentfontfamily && $this->FontStyle == $style && $this->FontStyle == $this->currentfontstyle && $this->FontSizePt == $size && $this->FontSizePt == $this->currentfontsize && !$forcewrite) {
				return $family;
			}

			$fontkey = $family . $style;

			// Select it
			$this->FontFamily = $family;
			$this->FontStyle = $style;
			$this->FontSizePt = $size;
			$this->FontSize = $size / Mpdf::SCALE;
			$this->CurrentFont = &$this->fonts[$fontkey];
			if ($write) {
				$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
				if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
					$this->writer->write($fontout);
				}
				$this->pageoutput[$this->page]['Font'] = $fontout;
			}

			// Added - currentfont (lowercase) used in HTML2PDF
			$this->currentfontfamily = $family;
			$this->currentfontsize = $size;
			$this->currentfontstyle = $style;
			$this->setMBencoding('UTF-8');
		} else {  // if using core fonts
			if ($this->PDFA || $this->PDFX) {
				throw new \Mpdf\MpdfException('Core Adobe fonts cannot be embedded in mPDF (required for PDFA1-b or PDFX/1-a) - cannot use option to use core fonts.');
			}
			$this->setMBencoding('windows-1252');

			// Test if font is already selected
			if (($this->FontFamily == $family) and ( $this->FontStyle == $style) and ( $this->FontSizePt == $size) && !$forcewrite) {
				return $family;
			}

			if (!isset($this->CoreFonts[$fontkey])) {
				if (in_array($family, $this->serif_fonts)) {
					$family = 'ctimes';
				} elseif (in_array($family, $this->mono_fonts)) {
					$family = 'ccourier';
				} else {
					$family = 'chelvetica';
				}
				$this->usingCoreFont = true;
				$fontkey = $family . $style;
			}

			if (!isset($this->fonts[$fontkey])) {
				// STANDARD CORE FONTS
				if (isset($this->CoreFonts[$fontkey])) {
					// Load metric file
					$file = $family;
					if ($family == 'ctimes' || $family == 'chelvetica' || $family == 'ccourier') {
						$file .= strtolower($style);
					}
					require __DIR__ . '/../data/font/' . $file . '.php';
					if (!isset($cw)) {
						throw new \Mpdf\MpdfException(sprintf('Could not include font metric file "%s"', $file));
					}
					$i = count($this->fonts) + $this->extraFontSubsets + 1;
					$this->fonts[$fontkey] = ['i' => $i, 'type' => 'core', 'name' => $this->CoreFonts[$fontkey], 'desc' => $desc, 'up' => $up, 'ut' => $ut, 'cw' => $cw];
					if ($this->useKerning && isset($kerninfo)) {
						$this->fonts[$fontkey]['kerninfo'] = $kerninfo;
					}
				} else {
					throw new \Mpdf\MpdfException(sprintf('Font %s not defined', $fontkey));
				}
			}

			// Test if font is already selected
			if (($this->FontFamily == $family) and ( $this->FontStyle == $style) and ( $this->FontSizePt == $size) && !$forcewrite) {
				return $family;
			}
			// Select it
			$this->FontFamily = $family;
			$this->FontStyle = $style;
			$this->FontSizePt = $size;
			$this->FontSize = $size / Mpdf::SCALE;
			$this->CurrentFont = &$this->fonts[$fontkey];
			if ($write) {
				$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
				if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
					$this->writer->write($fontout);
				}
				$this->pageoutput[$this->page]['Font'] = $fontout;
			}
			// Added - currentfont (lowercase) used in HTML2PDF
			$this->currentfontfamily = $family;
			$this->currentfontsize = $size;
			$this->currentfontstyle = $style;
		}

		return $family;
	}

	function SetFontSize($size, $write = true)
	{
		// Set font size in points
		if ($this->FontSizePt == $size) {
			return;
		}
		$this->FontSizePt = $size;
		$this->FontSize = $size / Mpdf::SCALE;
		$this->currentfontsize = $size;
		if ($write) {
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			// Edited mPDF 3.0
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
				$this->writer->write($fontout);
			}
			$this->pageoutput[$this->page]['Font'] = $fontout;
		}
	}

	function AddLink()
	{
		// Create a new internal link
		$n = count($this->links) + 1;
		$this->links[$n] = [0, 0];
		return $n;
	}

	function SetLink($link, $y = 0, $page = -1)
	{
		// Set destination of internal link
		if ($y == -1) {
			$y = $this->y;
		}
		if ($page == -1) {
			$page = $this->page;
		}
		$this->links[$link] = [$page, $y];
	}

	function Link($x, $y, $w, $h, $link)
	{
		$l = [$x * Mpdf::SCALE, $this->hPt - $y * Mpdf::SCALE, $w * Mpdf::SCALE, $h * Mpdf::SCALE, $link];
		if ($this->keep_block_together) { // don't write yet
			return;
		} elseif ($this->table_rotate) { // *TABLES*
			$this->tbrot_Links[$this->page][] = $l; // *TABLES*
			return; // *TABLES*
		} // *TABLES*
		elseif ($this->kwt) {
			$this->kwt_Links[$this->page][] = $l;
			return;
		}

		if ($this->writingHTMLheader || $this->writingHTMLfooter) {
			$this->HTMLheaderPageLinks[] = $l;
			return;
		}
		// Put a link on the page
		$this->PageLinks[$this->page][] = $l;
		// Save cross-reference to Column buffer
		$ref = count($this->PageLinks[$this->page]) - 1; // *COLUMNS*
		$this->columnLinks[$this->CurrCol][(int) $this->x][(int) $this->y] = $ref; // *COLUMNS*
	}

	function Text($x, $y, $txt, $OTLdata = [], $textvar = 0, $aixextra = '', $coordsys = '', $return = false)
	{
		// Output (or return) a string
		// Called (internally) by Watermark() & _tableWrite() [rotated cells] & TableHeaderFooter() & WriteText()
		// Called also from classes/svg.php
		// Expects Font to be set
		// Expects input to be mb_encoded if necessary and RTL reversed & OTL processed
		// ARTIFICIAL BOLD AND ITALIC
		$s = 'q ';
		if ($this->falseBoldWeight && strpos($this->ReqFontStyle, "B") !== false && strpos($this->FontStyle, "B") === false) {
			$s .= '2 Tr 1 J 1 j ';
			$s .= sprintf('%.3F w ', ($this->FontSize / 130) * Mpdf::SCALE * $this->falseBoldWeight);
			$tc = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if ($this->FillColor != $tc) {
				$s .= $tc . ' ';
			}  // stroke (outline) = same colour as text(fill)
		}
		if (strpos($this->ReqFontStyle, "I") !== false && strpos($this->FontStyle, "I") === false) {
			$aix = '1 0 0.261799 1 %.3F %.3F Tm';
		} else {
			$aix = '%.3F %.3F Td';
		}

		$aix = $aixextra . $aix;

		if ($this->ColorFlag) {
			$s .= $this->TextColor . ' ';
		}

		$this->CurrentFont['used'] = true;

		if ($this->usingCoreFont) {
			$txt2 = str_replace(chr(160), chr(32), $txt);
		} else {
			$txt2 = str_replace(chr(194) . chr(160), chr(32), $txt);
		}

		$px = $x;
		$py = $y;
		if ($coordsys != 'SVG') {
			$px = $x * Mpdf::SCALE;
			$py = ($this->h - $y) * Mpdf::SCALE;
		}


		/** ************** SIMILAR TO Cell() ************************ */

		// IF corefonts AND NOT SmCaps AND NOT Kerning
		// Just output text
		if ($this->usingCoreFont && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING)) {
			$txt2 = $this->writer->escape($txt2);
			$s .= sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
		} // IF NOT corefonts [AND NO wordspacing] AND NOT SIP/SMP AND NOT SmCaps AND NOT Kerning AND NOT OTL
		// Just output text
		elseif (!$this->usingCoreFont && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING) && !(isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && !empty($OTLdata['GPOSinfo']))) {
			// IF SIP/SMP
			if ($this->CurrentFont['sip'] || $this->CurrentFont['smp']) {
				$txt2 = $this->UTF8toSubset($txt2);
				$s .=sprintf('BT ' . $aix . ' %s Tj ET', $px, $py, $txt2);
			} // NOT SIP/SMP
			else {
				$txt2 = $this->writer->utf8ToUtf16BigEndian($txt2, false);
				$txt2 = $this->writer->escape($txt2);
				$s .=sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
			}
		} // IF NOT corefonts [AND IS wordspacing] AND NOT SIP AND NOT SmCaps AND NOT Kerning AND NOT OTL
		// Not required here (cf. Cell() )
		// ELSE (IF SmCaps || Kerning || OTL) [corefonts or not corefonts; SIP or SMP or BMP]
		else {
			$s .= $this->applyGPOSpdf($txt2, $aix, $px, $py, $OTLdata, $textvar);
		}
		/*         * ************** END ************************ */

		$s .= ' ';

		if (($textvar & TextVars::FD_UNDERLINE) && $txt != '') { // mPDF 5.7.1
			$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if ($this->FillColor != $c) {
				$s.= ' ' . $c . ' ';
			}
			if (isset($this->CurrentFont['up']) && $this->CurrentFont['up']) {
				$up = $this->CurrentFont['up'];
			} else {
				$up = -100;
			}
			$adjusty = (-$up / 1000 * $this->FontSize);
			if (isset($this->CurrentFont['ut']) && $this->CurrentFont['ut']) {
				$ut = $this->CurrentFont['ut'] / 1000 * $this->FontSize;
			} else {
				$ut = 60 / 1000 * $this->FontSize;
			}
			$olw = $this->LineWidth;
			$s .= ' ' . (sprintf(' %.3F w', $ut * Mpdf::SCALE));
			$s .= ' ' . $this->_dounderline($x, $y + $adjusty, $txt, $OTLdata, $textvar);
			$s .= ' ' . (sprintf(' %.3F w', $olw * Mpdf::SCALE));
			if ($this->FillColor != $c) {
				$s.= ' ' . $this->FillColor . ' ';
			}
		}
		// STRIKETHROUGH
		if (($textvar & TextVars::FD_LINETHROUGH) && $txt != '') { // mPDF 5.7.1
			$c = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
			if ($this->FillColor != $c) {
				$s.= ' ' . $c . ' ';
			}
			// Superscript and Subscript Y coordinate adjustment (now for striked-through texts)
			if (isset($this->CurrentFont['desc']['CapHeight']) && $this->CurrentFont['desc']['CapHeight']) {
				$ch = $this->CurrentFont['desc']['CapHeight'];
			} else {
				$ch = 700;
			}
			$adjusty = (-$ch / 1000 * $this->FontSize) * 0.35;
			if (isset($this->CurrentFont['ut']) && $this->CurrentFont['ut']) {
				$ut = $this->CurrentFont['ut'] / 1000 * $this->FontSize;
			} else {
				$ut = 60 / 1000 * $this->FontSize;
			}
			$olw = $this->LineWidth;
			$s .= ' ' . (sprintf(' %.3F w', $ut * Mpdf::SCALE));
			$s .= ' ' . $this->_dounderline($x, $y + $adjusty, $txt, $OTLdata, $textvar);
			$s .= ' ' . (sprintf(' %.3F w', $olw * Mpdf::SCALE));
			if ($this->FillColor != $c) {
				$s.= ' ' . $this->FillColor . ' ';
			}
		}
		$s .= 'Q';

		if ($return) {
			return $s . " \n";
		}
		$this->writer->write($s);
	}

	/* -- DIRECTW -- */

	function WriteText($x, $y, $txt)
	{
		// Output a string using Text() but does encoding and text reversing of RTL
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if ($this->usingCoreFont) {
			$txt = mb_convert_encoding($txt, $this->mb_enc, 'UTF-8');
		}

		// DIRECTIONALITY
		if (preg_match("/([" . $this->pregRTLchars . "])/u", $txt)) {
			$this->biDirectional = true;
		} // *OTL*

		$textvar = 0;
		$save_OTLtags = $this->OTLtags;
		$this->OTLtags = [];
		if ($this->useKerning) {
			if ($this->CurrentFont['haskernGPOS']) {
				$this->OTLtags['Plus'] .= ' kern';
			} else {
				$textvar = ($textvar | TextVars::FC_KERNING);
			}
		}

		/* -- OTL -- */
		// Use OTL OpenType Table Layout - GSUB & GPOS
		if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
			$txt = $this->otl->applyOTL($txt, $this->CurrentFont['useOTL']);
			$OTLdata = $this->otl->OTLdata;
		}
		/* -- END OTL -- */
		$this->OTLtags = $save_OTLtags;

		$this->magic_reverse_dir($txt, $this->directionality, $OTLdata);

		$this->Text($x, $y, $txt, $OTLdata, $textvar);
	}

	function WriteCell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '', $currentx = 0)
	{
		// Output a cell using Cell() but does encoding and text reversing of RTL
		$txt = $this->purify_utf8_text($txt);
		if ($this->text_input_as_HTML) {
			$txt = $this->all_entities_to_utf8($txt);
		}
		if ($this->usingCoreFont) {
			$txt = mb_convert_encoding($txt, $this->mb_enc, 'UTF-8');
		}
		// DIRECTIONALITY
		if (preg_match("/([" . $this->pregRTLchars . "])/u", $txt)) {
			$this->biDirectional = true;
		} // *OTL*

		$textvar = 0;
		$save_OTLtags = $this->OTLtags;
		$this->OTLtags = [];
		if ($this->useKerning) {
			if ($this->CurrentFont['haskernGPOS']) {
				$this->OTLtags['Plus'] .= ' kern';
			} else {
				$textvar = ($textvar | TextVars::FC_KERNING);
			}
		}

		/* -- OTL -- */
		// Use OTL OpenType Table Layout - GSUB & GPOS
		if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
			$txt = $this->otl->applyOTL($txt, $this->CurrentFont['useOTL']);
			$OTLdata = $this->otl->OTLdata;
		}
		/* -- END OTL -- */
		$this->OTLtags = $save_OTLtags;

		$this->magic_reverse_dir($txt, $this->directionality, $OTLdata);

		$this->Cell($w, $h, $txt, $border, $ln, $align, $fill, $link, $currentx, 0, 0, 'M', 0, false, $OTLdata, $textvar);
	}

	/* -- END DIRECTW -- */

	function ResetSpacing()
	{
		if ($this->ws != 0) {
			$this->writer->write('BT 0 Tw ET');
		}
		$this->ws = 0;
		if ($this->charspacing != 0) {
			$this->writer->write('BT 0 Tc ET');
		}
		$this->charspacing = 0;
	}

	function SetSpacing($cs, $ws)
	{
		if (intval($cs * 1000) == 0) {
			$cs = 0;
		}
		if ($cs) {
			$this->writer->write(sprintf('BT %.3F Tc ET', $cs));
		} elseif ($this->charspacing != 0) {
			$this->writer->write('BT 0 Tc ET');
		}
		$this->charspacing = $cs;
		if (intval($ws * 1000) == 0) {
			$ws = 0;
		}
		if ($ws) {
			$this->writer->write(sprintf('BT %.3F Tw ET', $ws));
		} elseif ($this->ws != 0) {
			$this->writer->write('BT 0 Tw ET');
		}
		$this->ws = $ws;
	}

	// WORD SPACING
	function GetJspacing($nc, $ns, $w, $inclCursive, &$cOTLdata)
	{
		$kashida_present = false;
		$kashida_space = 0;
		if ($w > 0 && $inclCursive && isset($this->CurrentFont['useKashida']) && $this->CurrentFont['useKashida'] && !empty($cOTLdata)) {
			for ($c = 0; $c < count($cOTLdata); $c++) {
				for ($i = 0; $i < strlen($cOTLdata[$c]['group']); $i++) {
					if (isset($cOTLdata[$c]['GPOSinfo'][$i]['kashida']) && $cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > 0) {
						$kashida_present = true;
						break 2;
					}
				}
			}
		}

		if ($kashida_present) {
			$k_ctr = 0;  // Number of kashida points
			$k_total = 0;  // Total of kashida values (priority)
			// Reset word
			$max_kashida_in_word = 0;
			$last_kashida_in_word = -1;

			for ($c = 0; $c < count($cOTLdata); $c++) {
				for ($i = 0; $i < strlen($cOTLdata[$c]['group']); $i++) {
					if ($cOTLdata[$c]['group'][$i] == 'S') {
						// Save from last word
						if ($max_kashida_in_word) {
							$k_ctr++;
							$k_total = $max_kashida_in_word;
						}
						// Reset word
						$max_kashida_in_word = 0;
						$last_kashida_in_word = -1;
					}

					if (isset($cOTLdata[$c]['GPOSinfo'][$i]['kashida']) && $cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > 0) {
						if ($max_kashida_in_word) {
							if ($cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > $max_kashida_in_word) {
								$max_kashida_in_word = $cOTLdata[$c]['GPOSinfo'][$i]['kashida'];
								$cOTLdata[$c]['GPOSinfo'][$last_kashida_in_word]['kashida'] = 0;
								$last_kashida_in_word = $i;
							} else {
								$cOTLdata[$c]['GPOSinfo'][$i]['kashida'] = 0;
							}
						} else {
							$max_kashida_in_word = $cOTLdata[$c]['GPOSinfo'][$i]['kashida'];
							$last_kashida_in_word = $i;
						}
					}
				}
			}
			// Save from last word
			if ($max_kashida_in_word) {
				$k_ctr++;
				$k_total = $max_kashida_in_word;
			}

			// Number of kashida points = $k_ctr
			// $useKashida is a % value from CurrentFont/config_fonts.php
			// % ratio divided between word-spacing and kashida-spacing
			$kashida_space_ratio = intval($this->CurrentFont['useKashida']) / 100;


			$kashida_space = $w * $kashida_space_ratio;

			$tatw = $this->_getCharWidth($this->CurrentFont['cw'], 0x0640);
			// Only use kashida if each allocated kashida width is > 0.01 x width of a tatweel
			// Otherwise fontstretch is too small and errors
			// If not just leave to adjust word-spacing
			if ($tatw && (($kashida_space / $k_ctr) / $tatw) > 0.01) {
				for ($c = 0; $c < count($cOTLdata); $c++) {
					for ($i = 0; $i < strlen($cOTLdata[$c]['group']); $i++) {
						if (isset($cOTLdata[$c]['GPOSinfo'][$i]['kashida']) && $cOTLdata[$c]['GPOSinfo'][$i]['kashida'] > 0) {
							// At this point kashida is a number representing priority (higher number - higher priority)
							// We are now going to set it as an actual length
							// This shares it equally amongst words:
							$cOTLdata[$c]['GPOSinfo'][$i]['kashida_space'] = (1 / $k_ctr) * $kashida_space;
						}
					}
				}
				$w -= $kashida_space;
			}
		}

		$ws = 0;
		$charspacing = 0;
		$ww = $this->jSWord;
		$ncx = $nc - 1;
		if ($nc == 0) {
			return [0, 0, 0];
		} // Only word spacing allowed / possible
		elseif ($this->fixedlSpacing !== false || $inclCursive) {
			if ($ns) {
				$ws = $w / $ns;
			}
		} elseif ($nc == 1) {
			$charspacing = $w;
		} elseif (!$ns) {
			$charspacing = $w / ($ncx );
			if (($this->jSmaxChar > 0) && ($charspacing > $this->jSmaxChar)) {
				$charspacing = $this->jSmaxChar;
			}
		} elseif ($ns == ($ncx )) {
			$charspacing = $w / $ns;
		} else {
			if ($this->usingCoreFont) {
				$cs = ($w * (1 - $this->jSWord)) / ($ncx );
				if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
					$cs = $this->jSmaxChar;
					$ww = 1 - (($cs * ($ncx )) / $w);
				}
				$charspacing = $cs;
				$ws = ($w * ($ww) ) / $ns;
			} else {
				$cs = ($w * (1 - $this->jSWord)) / ($ncx - $ns);
				if (($this->jSmaxChar > 0) && ($cs > $this->jSmaxChar)) {
					$cs = $this->jSmaxChar;
					$ww = 1 - (($cs * ($ncx - $ns)) / $w);
				}
				$charspacing = $cs;
				$ws = (($w * ($ww) ) / $ns) - $charspacing;
			}
		}
		return [$charspacing, $ws, $kashida_space];
	}

	/**
	 * Output a cell
	 *
	 * Expects input to be mb_encoded if necessary and RTL reversed
	 *
	 * @since mPDF 5.7.1
	 */
	function Cell($w, $h = 0, $txt = '', $border = 0, $ln = 0, $align = '', $fill = 0, $link = '', $currentx = 0, $lcpaddingL = 0, $lcpaddingR = 0, $valign = 'M', $spanfill = 0, $exactWidth = false, $OTLdata = false, $textvar = 0, $lineBox = false)
	{
		// NON_BREAKING SPACE
		if ($this->usingCoreFont) {
			$txt = str_replace(chr(160), chr(32), $txt);
		} else {
			$txt = str_replace(chr(194) . chr(160), chr(32), $txt);
		}

		$oldcolumn = $this->CurrCol;

		// Automatic page break
		// Allows PAGE-BREAK-AFTER = avoid to work
		if (isset($this->blk[$this->blklvl])) {
			$bottom = $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['margin_bottom'];
		} else {
			$bottom = 0;
		}

		if (!$this->tableLevel
			&& (
				($this->y + $this->divheight > $this->PageBreakTrigger)
				|| ($this->y + $h > $this->PageBreakTrigger)
				|| (
					$this->y + ($h * 2) + $bottom > $this->PageBreakTrigger
						&& (isset($this->blk[$this->blklvl]['page_break_after_avoid']) && $this->blk[$this->blklvl]['page_break_after_avoid'])
				)
			)
			&& !$this->InFooter
			&& $this->AcceptPageBreak()
		) { // mPDF 5.7.2

			$x = $this->x; // Current X position

			// WORD SPACING
			$ws = $this->ws; // Word Spacing
			$charspacing = $this->charspacing; // Character Spacing
			$this->ResetSpacing();

			$this->AddPage($this->CurOrientation);

			// Added to correct for OddEven Margins
			$x += $this->MarginCorrection;
			if ($currentx) {
				$currentx += $this->MarginCorrection;
			}
			$this->x = $x;
			// WORD SPACING
			$this->SetSpacing($charspacing, $ws);
		}

		// Test: to put line through centre of cell: $this->Line($this->x,$this->y+($h/2),$this->x+50,$this->y+($h/2));
		// Test: to put border around cell as it is specified: $border='LRTB';

		/* -- COLUMNS -- */
		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			if ($currentx) {
				$currentx += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			}
			$this->x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
		}

		// COLUMNS Update/overwrite the lowest bottom of printing y value for a column
		if ($this->ColActive) {
			if ($h) {
				$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y + $h;
			} else {
				$this->ColDetails[$this->CurrCol]['bottom_margin'] = $this->y + $this->divheight;
			}
		}
		/* -- END COLUMNS -- */


		if ($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		}

		$s = '';
		if ($fill == 1 && $this->FillColor) {
			if ((isset($this->pageoutput[$this->page]['FillColor']) && $this->pageoutput[$this->page]['FillColor'] != $this->FillColor) || !isset($this->pageoutput[$this->page]['FillColor'])) {
				$s .= $this->FillColor . ' ';
			}
			$this->pageoutput[$this->page]['FillColor'] = $this->FillColor;
		}

		if ($lineBox && isset($lineBox['boxtop']) && $txt) { // i.e. always from WriteFlowingBlock/finishFlowingBlock (but not objects -
			// which only have $lineBox['top'] set)
			$boxtop = $this->y + $lineBox['boxtop'];
			$boxbottom = $this->y + $lineBox['boxbottom'];
			$glyphYorigin = $lineBox['glyphYorigin'];
			$baseline_shift = $lineBox['baseline-shift'];
			$bord_boxtop = $bg_boxtop = $boxtop = $boxtop - $baseline_shift;
			$bord_boxbottom = $bg_boxbottom = $boxbottom = $boxbottom - $baseline_shift;
			$bord_boxheight = $bg_boxheight = $boxheight = $boxbottom - $boxtop;

			// If inline element BACKGROUND has bounding box set by parent element:
			if (isset($lineBox['background-boxtop'])) {
				$bg_boxtop = $this->y + $lineBox['background-boxtop'] - $lineBox['background-baseline-shift'];
				$bg_boxbottom = $this->y + $lineBox['background-boxbottom'] - $lineBox['background-baseline-shift'];
				$bg_boxheight = $bg_boxbottom - $bg_boxtop;
			}
			// If inline element BORDER has bounding box set by parent element:
			if (isset($lineBox['border-boxtop'])) {
				$bord_boxtop = $this->y + $lineBox['border-boxtop'] - $lineBox['border-baseline-shift'];
				$bord_boxbottom = $this->y + $lineBox['border-boxbottom'] - $lineBox['border-baseline-shift'];
				$bord_boxheight = $bord_boxbottom - $bord_boxtop;
			}

		} else {

			$boxtop = $this->y;
			$boxheight = $h;
			$boxbottom = $this->y + $h;
			$baseline_shift = 0;

			if ($txt != '') {

				// FONT SIZE - this determines the baseline caculation
				$bfs = $this->FontSize;
				// Calculate baseline Superscript and Subscript Y coordinate adjustment
				$bfx = $this->baselineC;
				$baseline = $bfx * $bfs;

				if ($textvar & TextVars::FA_SUPERSCRIPT) {
					$baseline_shift = $this->textparam['text-baseline'];
				} elseif ($textvar & TextVars::FA_SUBSCRIPT) {
					$baseline_shift = $this->textparam['text-baseline'];
				} elseif ($this->bullet) {
					$baseline += ($bfx - 0.7) * $this->FontSize;
				}

				// Vertical align (for Images)
				if ($valign == 'T') {
					$va = (0.5 * $bfs * $this->normalLineheight);
				} elseif ($valign == 'B') {
					$va = $h - (0.5 * $bfs * $this->normalLineheight);
				} else {
					$va = 0.5 * $h;
				} // Middle

				// ONLY SET THESE IF WANT TO CONFINE BORDER +/- FILL TO FIT FONTSIZE - NOT FULL CELL AS IS ORIGINAL FUNCTION
				// spanfill or spanborder are set in FlowingBlock functions
				if ($spanfill || !empty($this->spanborddet) || $link != '') {
					$exth = 0.2; // Add to fontsize to increase height of background / link / border
					$boxtop = $this->y + $baseline + $va - ($this->FontSize * (1 + $exth / 2) * (0.5 + $bfx));
					$boxheight = $this->FontSize * (1 + $exth);
					$boxbottom = $boxtop + $boxheight;
				}

				$glyphYorigin = $baseline + $va;
			}

			$boxtop -= $baseline_shift;
			$boxbottom -= $baseline_shift;
			$bord_boxtop = $bg_boxtop = $boxtop;
			$bord_boxbottom = $bg_boxbottom = $boxbottom;
			$bord_boxheight = $bg_boxheight = $boxheight = $boxbottom - $boxtop;
		}

		$bbw = $tbw = $lbw = $rbw = 0; // Border widths
		if (!empty($this->spanborddet)) {

			if (!isset($this->spanborddet['B'])) {
				$this->spanborddet['B'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			if (!isset($this->spanborddet['T'])) {
				$this->spanborddet['T'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			if (!isset($this->spanborddet['L'])) {
				$this->spanborddet['L'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			if (!isset($this->spanborddet['R'])) {
				$this->spanborddet['R'] = ['s' => 0, 'style' => '', 'w' => 0];
			}

			$bbw = $this->spanborddet['B']['w'];
			$tbw = $this->spanborddet['T']['w'];
			$lbw = $this->spanborddet['L']['w'];
			$rbw = $this->spanborddet['R']['w'];
		}

		if ($fill == 1 || $border == 1 || !empty($this->spanborddet)) {

			if (!empty($this->spanborddet)) {

				if ($fill == 1) {
					$s .= sprintf('%.3F %.3F %.3F %.3F re f ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bg_boxtop + $tbw) * Mpdf::SCALE, ($w + $lbw + $rbw) * Mpdf::SCALE, (-$bg_boxheight - $tbw - $bbw) * Mpdf::SCALE);
				}

				$s.= ' q ';
				$dashon = 3;
				$dashoff = 3.5;
				$dot = 2.5;

				if ($tbw) {
					$short = 0;

					if ($this->spanborddet['T']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $tbw * $dashon * Mpdf::SCALE, $tbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['T']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $tbw * $dot * Mpdf::SCALE, -$tbw / 2 * Mpdf::SCALE);
						$short = $tbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['T']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['T']['c'], true);

					if ($this->spanborddet['T']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $tbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw * 5 / 6) * Mpdf::SCALE, ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw * 5 / 6) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 6) * Mpdf::SCALE, ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 6) * Mpdf::SCALE);
					} elseif ($this->spanborddet['T']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $tbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $tbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw / 2) * Mpdf::SCALE);
					}

					if ($this->spanborddet['T']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}
				if ($bbw) {

					$short = 0;
					if ($this->spanborddet['B']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $bbw * $dashon * Mpdf::SCALE, $bbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['B']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $bbw * $dot * Mpdf::SCALE, -$bbw / 2 * Mpdf::SCALE);
						$short = $bbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['B']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['B']['c'], true);

					if ($this->spanborddet['B']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $bbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 6) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 6) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw * 5 / 6) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw * 5 / 6) * Mpdf::SCALE);
					} elseif ($this->spanborddet['B']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $bbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $bbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE, ($this->x + $w + $rbw - $short) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw / 2) * Mpdf::SCALE);
					}

					if ($this->spanborddet['B']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}

				if ($lbw) {
					$short = 0;
					if ($this->spanborddet['L']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $lbw * $dashon * Mpdf::SCALE, $lbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['L']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $lbw * $dot * Mpdf::SCALE, -$lbw / 2 * Mpdf::SCALE);
						$short = $lbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['L']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x - $lbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['L']['c'], true);
					if ($this->spanborddet['L']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $lbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} elseif ($this->spanborddet['L']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $lbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $lbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x - $lbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					}

					if ($this->spanborddet['L']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}

				if ($rbw) {

					$short = 0;
					if ($this->spanborddet['R']['style'] == 'dashed') {
						$s .= sprintf(' 0 j 0 J [%.3F %.3F] 0 d ', $rbw * $dashon * Mpdf::SCALE, $rbw * $dashoff * Mpdf::SCALE);
					} elseif ($this->spanborddet['R']['style'] == 'dotted') {
						$s .= sprintf(' 1 j 1 J [%.3F %.3F] %.3F d ', 0.001, $rbw * $dot * Mpdf::SCALE, -$rbw / 2 * Mpdf::SCALE);
						$short = $rbw / 2;
					} else {
						$s .= ' 0 j 0 J [] 0 d ';
					}

					if ($this->spanborddet['R']['style'] != 'dotted') {
						$s .= 'q ';
						$s .= sprintf('%.3F %.3F m ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxbottom) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F l ', ($this->x + $w + $rbw) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE);
						$s .= ' h W n '; // Ends path no-op & Sets the clipping path
					}

					$c = $this->SetDColor($this->spanborddet['R']['c'], true);
					if ($this->spanborddet['R']['style'] == 'double') {
						$s .= sprintf(' %s %.3F w ', $c, $rbw / 3 * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw * 5 / 6) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} elseif ($this->spanborddet['R']['style'] == 'dotted') {
						$s .= sprintf(' %s %.3F w ', $c, $rbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					} else {
						$s .= sprintf(' %s %.3F w ', $c, $rbw * Mpdf::SCALE);
						$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxtop + $tbw) * Mpdf::SCALE, ($this->x + $w + $rbw / 2) * Mpdf::SCALE, ($this->h - $bord_boxbottom - $bbw + $short) * Mpdf::SCALE);
					}

					if ($this->spanborddet['R']['style'] != 'dotted') {
						$s .= ' Q ';
					}
				}

				$s.= ' Q ';

			} else { // If "border", does not come from WriteFlowingBlock or FinishFlowingBlock

				if ($fill == 1) {
					$op = ($border == 1) ? 'B' : 'f';
				} else {
					$op = 'S';
				}

				$s .= sprintf('%.3F %.3F %.3F %.3F re %s ', $this->x * Mpdf::SCALE, ($this->h - $bg_boxtop) * Mpdf::SCALE, $w * Mpdf::SCALE, -$bg_boxheight * Mpdf::SCALE, $op);
			}
		}

		if (is_string($border)) { // If "border", does not come from WriteFlowingBlock or FinishFlowingBlock

			$x = $this->x;
			$y = $this->y;

			if (is_int(strpos($border, 'L'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', $x * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE);
			}

			if (is_int(strpos($border, 'T'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', $x * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE, ($x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE);
			}

			if (is_int(strpos($border, 'R'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', ($x + $w) * Mpdf::SCALE, ($this->h - $bord_boxtop) * Mpdf::SCALE, ($x + $w) * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE);
			}

			if (is_int(strpos($border, 'B'))) {
				$s .= sprintf('%.3F %.3F m %.3F %.3F l S ', $x * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE, ($x + $w) * Mpdf::SCALE, ($this->h - ($bord_boxbottom)) * Mpdf::SCALE);
			}
		}

		if ($txt != '') {

			if ($exactWidth) {
				$stringWidth = $w;
			} else {
				$stringWidth = $this->GetStringWidth($txt, true, $OTLdata, $textvar) + ( $this->charspacing * mb_strlen($txt, $this->mb_enc) / Mpdf::SCALE ) + ( $this->ws * mb_substr_count($txt, ' ', $this->mb_enc) / Mpdf::SCALE );
			}

			// Set x OFFSET FOR PRINTING
			if ($align == 'R') {
				$dx = $w - $this->cMarginR - $stringWidth - $lcpaddingR;
			} elseif ($align == 'C') {
				$dx = (($w - $stringWidth ) / 2);
			} elseif ($align == 'L' or $align == 'J') {
				$dx = $this->cMarginL + $lcpaddingL;
			} else {
				$dx = 0;
			}

			if ($this->ColorFlag) {
				$s .='q ' . $this->TextColor . ' ';
			}

			// OUTLINE
			if (isset($this->textparam['outline-s']) && $this->textparam['outline-s'] && !($textvar & TextVars::FC_SMALLCAPS)) { // mPDF 5.7.1
				$s .=' ' . sprintf('%.3F w', $this->LineWidth * Mpdf::SCALE) . ' ';
				$s .=" $this->DrawColor ";
				$s .=" 2 Tr ";
			} elseif ($this->falseBoldWeight && strpos($this->ReqFontStyle, "B") !== false && strpos($this->FontStyle, "B") === false && !($textvar & TextVars::FC_SMALLCAPS)) { // can't use together with OUTLINE or Small Caps	// mPDF 5.7.1	??? why not with SmallCaps ???
				$s .= ' 2 Tr 1 J 1 j ';
				$s .= ' ' . sprintf('%.3F w', ($this->FontSize / 130) * Mpdf::SCALE * $this->falseBoldWeight) . ' ';
				$tc = strtoupper($this->TextColor); // change 0 0 0 rg to 0 0 0 RG
				if ($this->FillColor != $tc) {
					$s .= ' ' . $tc . ' ';
				}  // stroke (outline) = same colour as text(fill)
			} else {
				$s .=" 0 Tr ";
			}

			if (strpos($this->ReqFontStyle, "I") !== false && strpos($this->FontStyle, "I") === false) { // Artificial italic
				$aix = '1 0 0.261799 1 %.3F %.3F Tm ';
			} else {
				$aix = '%.3F %.3F Td ';
			}

			$px = ($this->x + $dx) * Mpdf::SCALE;
			$py = ($this->h - ($this->y + $glyphYorigin - $baseline_shift)) * Mpdf::SCALE;

			// THE TEXT
			$txt2 = $txt;
			$sub = '';
			$this->CurrentFont['used'] = true;

			/*             * ************** SIMILAR TO Text() ************************ */

			// IF corefonts AND NOT SmCaps AND NOT Kerning
			// Just output text; charspacing and wordspacing already set by charspacing (Tc) and ws (Tw)
			if ($this->usingCoreFont && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING)) {
				$txt2 = $this->writer->escape($txt2);
				$sub .= sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
			} // IF NOT corefonts AND NO wordspacing AND NOT SIP/SMP AND NOT SmCaps AND NOT Kerning AND NOT OTL
			// Just output text
			elseif (!$this->usingCoreFont && !$this->ws && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING) && !(isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && !empty($OTLdata['GPOSinfo']))) {
				// IF SIP/SMP
				if ((isset($this->CurrentFont['sip']) && $this->CurrentFont['sip']) || (isset($this->CurrentFont['smp']) && $this->CurrentFont['smp'])) {
					$txt2 = $this->UTF8toSubset($txt2);
					$sub .=sprintf('BT ' . $aix . ' %s Tj ET', $px, $py, $txt2);
				} // NOT SIP/SMP
				else {
					$txt2 = $this->writer->utf8ToUtf16BigEndian($txt2, false);
					$txt2 = $this->writer->escape($txt2);
					$sub .=sprintf('BT ' . $aix . ' (%s) Tj ET', $px, $py, $txt2);
				}
			} // IF NOT corefonts AND IS wordspacing AND NOT SIP AND NOT SmCaps AND NOT Kerning AND NOT OTL
			// Output text word by word with an adjustment to the intercharacter spacing for SPACEs to form word spacing
			// IF multibyte - Tw has no effect - need to do word spacing using an adjustment before each space
			elseif (!$this->usingCoreFont && $this->ws && !((isset($this->CurrentFont['sip']) && $this->CurrentFont['sip']) || (isset($this->CurrentFont['smp']) && $this->CurrentFont['smp'])) && !($textvar & TextVars::FC_SMALLCAPS) && !($textvar & TextVars::FC_KERNING) && !(isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF) && (!empty($OTLdata['GPOSinfo']) || (strpos($OTLdata['group'], 'M') !== false && $this->charspacing)) )) {
				$space = " ";
				$space = $this->writer->utf8ToUtf16BigEndian($space, false);
				$space = $this->writer->escape($space);
				$sub .=sprintf('BT ' . $aix . ' %.3F Tc [', $px, $py, $this->charspacing);
				$t = explode(' ', $txt2);
				$numt = count($t);
				for ($i = 0; $i < $numt; $i++) {
					$tx = $t[$i];
					$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
					$tx = $this->writer->escape($tx);
					$sub .=sprintf('(%s) ', $tx);
					if (($i + 1) < $numt) {
						$adj = -($this->ws) * 1000 / $this->FontSizePt;
						$sub .=sprintf('%d(%s) ', $adj, $space);
					}
				}
				$sub .='] TJ ';
				$sub .=' ET';
			} // ELSE (IF SmCaps || Kerning || OTL) [corefonts or not corefonts; SIP or SMP or BMP]
			else {
				$sub = $this->applyGPOSpdf($txt, $aix, $px, $py, $OTLdata, $textvar);
			}

			/** ************** END SIMILAR TO Text() ************************ */

			if ($this->shrin_k > 1) {
				$shrin_k = $this->shrin_k;
			} else {
				$shrin_k = 1;
			}

			// UNDERLINE
			if ($textvar & TextVars::FD_UNDERLINE) { // mPDF 5.7.1	// mPDF 6

				// mPDF 5.7.3  inline text-decoration parameters

				$c = isset($this->textparam['u-decoration']['color']) ? $this->textparam['u-decoration']['color'] : '';
				if ($this->FillColor != $c) {
					$sub .= ' ' . $c . ' ';
				}

				// mPDF 5.7.3  inline text-decoration parameters
				$decorationfontkey = isset($this->textparam['u-decoration']['fontkey']) ? $this->textparam['u-decoration']['fontkey'] : '';
				$decorationfontsize = isset($this->textparam['u-decoration']['fontsize']) ? $this->textparam['u-decoration']['fontsize'] / $shrin_k : 0;

				if (isset($this->fonts[$decorationfontkey]['ut']) && $this->fonts[$decorationfontkey]['ut']) {
					$ut = $this->fonts[$decorationfontkey]['ut'] / 1000 * $decorationfontsize;
				} else {
					$ut = 60 / 1000 * $decorationfontsize;
				}

				if (isset($this->fonts[$decorationfontkey]['up']) && $this->fonts[$decorationfontkey]['up']) {
					$up = $this->fonts[$decorationfontkey]['up'];
				} else {
					$up = -100;
				}

				$adjusty = (-$up / 1000 * $decorationfontsize) + $ut / 2;
				$ubaseline = isset($this->textparam['u-decoration']['baseline'])
					? $glyphYorigin - $this->textparam['u-decoration']['baseline'] / $shrin_k
					: $glyphYorigin;

				$olw = $this->LineWidth;

				$sub .= ' ' . (sprintf(' %.3F w 0 j 0 J ', $ut * Mpdf::SCALE));
				$sub .= ' ' . $this->_dounderline($this->x + $dx, $this->y + $ubaseline + $adjusty, $txt, $OTLdata, $textvar);
				$sub .= ' ' . (sprintf(' %.3F w 2 j 2 J ', $olw * Mpdf::SCALE));

				if ($this->FillColor != $c) {
					$sub .= ' ' . $this->FillColor . ' ';
				}
			}

			// STRIKETHROUGH
			if ($textvar & TextVars::FD_LINETHROUGH) { // mPDF 5.7.1	// mPDF 6

				// mPDF 5.7.3  inline text-decoration parameters
				$c = $this->textparam['s-decoration']['color'];

				if ($this->FillColor != $c) {
					$sub .= ' ' . $c . ' ';
				}

				// mPDF 5.7.3  inline text-decoration parameters
				$decorationfontkey = $this->textparam['s-decoration']['fontkey'];
				$decorationfontsize = $this->textparam['s-decoration']['fontsize'] / $shrin_k;

				// Use yStrikeoutSize from OS/2 if available
				if (isset($this->fonts[$decorationfontkey]['strs']) && $this->fonts[$decorationfontkey]['strs']) {
					$ut = $this->fonts[$decorationfontkey]['strs'] / 1000 * $decorationfontsize;
				} // else use underlineThickness from post if available
				elseif (isset($this->fonts[$decorationfontkey]['ut']) && $this->fonts[$decorationfontkey]['ut']) {
					$ut = $this->fonts[$decorationfontkey]['ut'] / 1000 * $decorationfontsize;
				} else {
					$ut = 50 / 1000 * $decorationfontsize;
				}

				// Use yStrikeoutPosition from OS/2 if available
				if (isset($this->fonts[$decorationfontkey]['strp']) && $this->fonts[$decorationfontkey]['strp']) {
					$up = $this->fonts[$decorationfontkey]['strp'];
					$adjusty = (-$up / 1000 * $decorationfontsize);
				} // else use a fraction ($this->baselineS) of CapHeight
				else {
					if (isset($this->fonts[$decorationfontkey]['desc']['CapHeight']) && $this->fonts[$decorationfontkey]['desc']['CapHeight']) {
						$ch = $this->fonts[$decorationfontkey]['desc']['CapHeight'];
					} else {
						$ch = 700;
					}
					$adjusty = (-$ch / 1000 * $decorationfontsize) * $this->baselineS;
				}

				$sbaseline = $glyphYorigin - $this->textparam['s-decoration']['baseline'] / $shrin_k;

				$olw = $this->LineWidth;

				$sub .=' ' . (sprintf(' %.3F w 0 j 0 J ', $ut * Mpdf::SCALE));
				$sub .=' ' . $this->_dounderline($this->x + $dx, $this->y + $sbaseline + $adjusty, $txt, $OTLdata, $textvar);
				$sub .=' ' . (sprintf(' %.3F w 2 j 2 J ', $olw * Mpdf::SCALE));

				if ($this->FillColor != $c) {
					$sub .= ' ' . $this->FillColor . ' ';
				}
			}

			// mPDF 5.7.3  inline text-decoration parameters
			// OVERLINE
			if ($textvar & TextVars::FD_OVERLINE) { // mPDF 5.7.1	// mPDF 6
				// mPDF 5.7.3  inline text-decoration parameters
				$c = $this->textparam['o-decoration']['color'];
				if ($this->FillColor != $c) {
					$sub .= ' ' . $c . ' ';
				}

				// mPDF 5.7.3  inline text-decoration parameters
				$decorationfontkey = (int) (((float) $this->textparam['o-decoration']['fontkey']) / $shrin_k);
				$decorationfontsize = $this->textparam['o-decoration']['fontsize'];

				if (isset($this->fonts[$decorationfontkey]['ut']) && $this->fonts[$decorationfontkey]['ut']) {
					$ut = $this->fonts[$decorationfontkey]['ut'] / 1000 * $decorationfontsize;
				} else {
					$ut = 60 / 1000 * $decorationfontsize;
				}
				if (isset($this->fonts[$decorationfontkey]['desc']['CapHeight']) && $this->fonts[$decorationfontkey]['desc']['CapHeight']) {
					$ch = $this->fonts[$decorationfontkey]['desc']['CapHeight'];
				} else {
					$ch = 700;
				}
				$adjusty = (-$ch / 1000 * $decorationfontsize) * $this->baselineO;
				$obaseline = $glyphYorigin - $this->textparam['o-decoration']['baseline'] / $shrin_k;
				$olw = $this->LineWidth;
				$sub .=' ' . (sprintf(' %.3F w 0 j 0 J ', $ut * Mpdf::SCALE));
				$sub .=' ' . $this->_dounderline($this->x + $dx, $this->y + $obaseline + $adjusty, $txt, $OTLdata, $textvar);
				$sub .=' ' . (sprintf(' %.3F w 2 j 2 J ', $olw * Mpdf::SCALE));
				if ($this->FillColor != $c) {
					$sub .= ' ' . $this->FillColor . ' ';
				}
			}

			// TEXT SHADOW
			if ($this->textshadow) {  // First to process is last in CSS comma separated shadows
				foreach ($this->textshadow as $ts) {
					$s .= ' q ';
					$s .= $this->SetTColor($ts['col'], true) . "\n";
					if ($ts['col'][0] == 5 && ord($ts['col'][4]) < 100) { // RGBa
						$s .= $this->SetAlpha(ord($ts['col'][4]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($ts['col'][0] == 6 && ord($ts['col'][5]) < 100) { // CMYKa
						$s .= $this->SetAlpha(ord($ts['col'][5]) / 100, 'Normal', true, 'F') . "\n";
					} elseif ($ts['col'][0] == 1 && $ts['col'][2] == 1 && ord($ts['col'][3]) < 100) { // Gray
						$s .= $this->SetAlpha(ord($ts['col'][3]) / 100, 'Normal', true, 'F') . "\n";
					}
					$s .= sprintf(' 1 0 0 1 %.4F %.4F cm', $ts['x'] * Mpdf::SCALE, -$ts['y'] * Mpdf::SCALE) . "\n";
					$s .= $sub;
					$s .= ' Q ';
				}
			}

			$s .= $sub;

			// COLOR
			if ($this->ColorFlag) {
				$s .=' Q';
			}

			// LINK
			if ($link != '') {
				$this->Link($this->x, $boxtop, $w, $boxheight, $link);
			}
		}
		if ($s) {
			$this->writer->write($s);
		}

		// WORD SPACING
		if ($this->ws && !$this->usingCoreFont) {
			$this->writer->write(sprintf('BT %.3F Tc ET', $this->charspacing));
		}
		$this->lasth = $h;
		if (strpos($txt, "\n") !== false) {
			$ln = 1; // cell recognizes \n from <BR> tag
		}
		if ($ln > 0) {
			// Go to next line
			$this->y += $h;
			if ($ln == 1) {
				// Move to next line
				if ($currentx != 0) {
					$this->x = $currentx;
				} else {
					$this->x = $this->lMargin;
				}
			}
		} else {
			$this->x+=$w;
		}
	}

	function applyGPOSpdf($txt, $aix, $x, $y, $OTLdata, $textvar = 0)
	{
		$sipset = (isset($this->CurrentFont['sip']) && $this->CurrentFont['sip'])
			|| (isset($this->CurrentFont['smp']) && $this->CurrentFont['smp']);

		$smcaps = ($textvar & TextVars::FC_SMALLCAPS);

		$fontid = $sipset
			? $last_fontid = $original_fontid = $this->CurrentFont['subsetfontids'][0]
			: $last_fontid = $original_fontid = $this->CurrentFont['i'];

		$SmallCapsON = false;  // state: uppercase/not
		$lastSmallCapsON = false; // state: uppercase/not
		$last_fontsize = $fontsize = $this->FontSizePt;
		$last_fontstretch = $fontstretch = 100;
		$groupBreak = false;

		$unicode = $this->UTF8StringToArray($txt);

		$GPOSinfo = (isset($OTLdata['GPOSinfo']) ? $OTLdata['GPOSinfo'] : []);
		$charspacing = ($this->charspacing * 1000 / $this->FontSizePt);
		$wordspacing = ($this->ws * 1000 / $this->FontSizePt);

		$XshiftBefore = 0;
		$XshiftAfter = 0;
		$lastYPlacement = 0;

		$tj = $sipset
			? '<'
			: '(';

		for ($i = 0; $i < count($unicode); $i++) {
			$c = $unicode[$i];
			$tx = '';
			$XshiftBefore = $XshiftAfter;
			$XshiftAfter = 0;
			$YPlacement = 0;
			$groupBreak = false;
			$kashida = 0;
			if (!empty($OTLdata)) {
				// YPlacement from GPOS
				if (isset($GPOSinfo[$i]['YPlacement']) && $GPOSinfo[$i]['YPlacement']) {
					$YPlacement = $GPOSinfo[$i]['YPlacement'] * $this->FontSizePt / $this->CurrentFont['unitsPerEm'];
					$groupBreak = true;
				}
				// XPlacement from GPOS
				if (isset($GPOSinfo[$i]['XPlacement']) && $GPOSinfo[$i]['XPlacement']) {
					if (!isset($GPOSinfo[$i]['wDir']) || $GPOSinfo[$i]['wDir'] !== 'RTL') {
						if (isset($GPOSinfo[$i]['BaseWidth'])) {
							$GPOSinfo[$i]['XPlacement'] -= $GPOSinfo[$i]['BaseWidth'];
						}
					}

					// Convert to PDF Text space (thousandths of a unit );
					$XshiftBefore += $GPOSinfo[$i]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
					$XshiftAfter += -$GPOSinfo[$i]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
				}

				// Kashida from GPOS
				// Kashida is set as an absolute length value, but to adjust text needs to be converted to
				// font-related size
				if (isset($GPOSinfo[$i]['kashida_space']) && $GPOSinfo[$i]['kashida_space']) {
					$kashida = $GPOSinfo[$i]['kashida_space'];
				}

				if ($c == 32) { // word spacing
					$XshiftAfter += $wordspacing;
				}

				if (substr($OTLdata['group'], ($i + 1), 1) !== 'M') { // Don't add inter-character spacing before Marks
					$XshiftAfter += $charspacing;
				}

				// ...applyGPOSpdf...
				// XAdvance from GPOS - Convert to PDF Text space (thousandths of a unit );
				if (((isset($GPOSinfo[$i]['wDir']) && $GPOSinfo[$i]['wDir'] !== 'RTL') || !isset($GPOSinfo[$i]['wDir'])) && isset($GPOSinfo[$i]['XAdvanceL']) && $GPOSinfo[$i]['XAdvanceL']) {
					$XshiftAfter += $GPOSinfo[$i]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
				} elseif (isset($GPOSinfo[$i]['wDir']) && $GPOSinfo[$i]['wDir'] === 'RTL' && isset($GPOSinfo[$i]['XAdvanceR']) && $GPOSinfo[$i]['XAdvanceR']) {
					$XshiftAfter += $GPOSinfo[$i]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
				}

			} else { // Character & Word spacing - if NOT OTL
				$XshiftAfter += $charspacing;
				if ($c == 32) {
					$XshiftAfter += $wordspacing;
				}
			}

			// IF Kerning done using pairs rather than OTL
			if ($textvar & TextVars::FC_KERNING) {
				if ($i > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$unicode[$i]])) {
					$XshiftBefore += $this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$unicode[$i]];
				}
			}

			if ($YPlacement !== $lastYPlacement) {
				$groupBreak = true;
			}

			if ($XshiftBefore) {  // +ve value in PDF moves to the left
				// If Fontstretch is ongoing, need to adjust X adjustments because these will be stretched out.
				$XshiftBefore *= 100 / $last_fontstretch;
				if ($sipset) {
					$tj .= sprintf('>%d<', (-$XshiftBefore));
				} else {
					$tj .= sprintf(')%d(', (-$XshiftBefore));
				}
			}

			// Small-Caps
			if ($smcaps) {
				if (isset($this->upperCase[$c])) {
					$c = $this->upperCase[$c];
					// $this->CurrentFont['subset'][$this->upperCase[$c]] = $this->upperCase[$c];	// add the CAP to subset
					$SmallCapsON = true;
					// For $sipset
					if (!$lastSmallCapsON) { // Turn ON SmallCaps
						$groupBreak = true;
						$fontstretch = $this->smCapsStretch;
						$fontsize = $this->FontSizePt * $this->smCapsScale;
					}
				} else {
					$SmallCapsON = false;
					if ($lastSmallCapsON) {  // Turn OFF SmallCaps
						$groupBreak = true;
						$fontstretch = 100;
						$fontsize = $this->FontSizePt;
					}
				}
			}

			// Prepare Text and Select Font ID
			if ($sipset) {
				for ($j = 0; $j < 99; $j++) {
					$init = array_search($c, $this->CurrentFont['subsets'][$j]);
					if ($init !== false) {
						if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
							$groupBreak = true;
							$fontid = $this->CurrentFont['subsetfontids'][$j];
						}
						$tx = sprintf("%02s", strtoupper(dechex($init)));

						break;
					}

					if (count($this->CurrentFont['subsets'][$j]) < 255) {
						$n = count($this->CurrentFont['subsets'][$j]);
						$this->CurrentFont['subsets'][$j][$n] = $c;
						if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
							$groupBreak = true;
							$fontid = $this->CurrentFont['subsetfontids'][$j];
						}
						$tx = sprintf("%02s", strtoupper(dechex($n)));

						break;
					}

					if (!isset($this->CurrentFont['subsets'][($j + 1)])) {
						$this->CurrentFont['subsets'][($j + 1)] = [0 => 0];
						$this->CurrentFont['subsetfontids'][($j + 1)] = count($this->fonts) + $this->extraFontSubsets + 1;
						$this->extraFontSubsets++;
					}
				}

			} else {

				$tx = UtfString::code2utf($c);

				if ($this->usingCoreFont) {
					$tx = iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $tx);
				} else {
					$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
				}

				$tx = $this->writer->escape($tx);

			}

			// If any settings require a new Text Group
			if ($groupBreak || $fontstretch != $last_fontstretch) {

				$tj .= $sipset
					? '>] TJ '
					: ')] TJ ';

				if ($fontid != $last_fontid || $fontsize != $last_fontsize) {
					$tj .= sprintf(' /F%d %.3F Tf ', $fontid, $fontsize);
				}

				if ($fontstretch != $last_fontstretch) {
					$tj .= sprintf('%d Tz ', $fontstretch);
				}

				if ($YPlacement != $lastYPlacement) {
					$tj .= sprintf('%.3F Ts ', $YPlacement);
				}

				$tj .= $sipset
					? '[<'
					: '[(';
			}

			// Output the code for the txt character
			$tj .= $tx;
			$lastSmallCapsON = $SmallCapsON;
			$last_fontid = $fontid;
			$last_fontsize = $fontsize;
			$last_fontstretch = $fontstretch;

			// Kashida
			if ($kashida) {
				$c = 0x0640; // add the Tatweel U+0640
				if (isset($this->CurrentFont['subset'])) {
					$this->CurrentFont['subset'][$c] = $c;
				}
				$kashida *= 1000 / $this->FontSizePt;
				$tatw = $this->_getCharWidth($this->CurrentFont['cw'], 0x0640);

				// Get YPlacement from next Base character
				$nextbase = $i + 1;

				while ($OTLdata['group'][$nextbase] !== 'C') {
					$nextbase++;
				}

				if (isset($GPOSinfo[$nextbase]) && isset($GPOSinfo[$nextbase]['YPlacement']) && $GPOSinfo[$nextbase]['YPlacement']) {
					$YPlacement = $GPOSinfo[$nextbase]['YPlacement'] * $this->FontSizePt / $this->CurrentFont['unitsPerEm'];
				}

				// Prepare Text and Select Font ID
				if ($sipset) {

					for ($j = 0; $j < 99; $j++) {

						$init = array_search($c, $this->CurrentFont['subsets'][$j]);

						if ($init !== false) {
							if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
								$fontid = $this->CurrentFont['subsetfontids'][$j];
							}
							$tx = sprintf("%02s", strtoupper(dechex($init)));

							break;
						}

						if (count($this->CurrentFont['subsets'][$j]) < 255) {
							$n = count($this->CurrentFont['subsets'][$j]);
							$this->CurrentFont['subsets'][$j][$n] = $c;
							if ($this->CurrentFont['subsetfontids'][$j] != $last_fontid) {
								$fontid = $this->CurrentFont['subsetfontids'][$j];
							}
							$tx = sprintf("%02s", strtoupper(dechex($n)));

							break;
						}

						if (!isset($this->CurrentFont['subsets'][($j + 1)])) {
							$this->CurrentFont['subsets'][($j + 1)] = [0 => 0];
							$this->CurrentFont['subsetfontids'][($j + 1)] = count($this->fonts) + $this->extraFontSubsets + 1;
							$this->extraFontSubsets++;
						}
					}
				} else {
					$tx = UtfString::code2utf($c);
					$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
					$tx = $this->writer->escape($tx);
				}

				if ($kashida > $tatw) {

					// Insert multiple tatweel characters, repositioning the last one to give correct total length

					$fontstretch = 100;
					$nt = (int) ($kashida / $tatw);
					$nudgeback = (($nt + 1) * $tatw) - $kashida;
					$optx = str_repeat($tx, $nt);

					if ($sipset) {
						$optx .= sprintf('>%d<', ($nudgeback));
					} else {
						$optx .= sprintf(')%d(', ($nudgeback));
					}

					$optx .= $tx; // #last

				} else {
					// Insert single tatweel character and use fontstretch to get correct length
					$fontstretch = ($kashida / $tatw) * 100;
					$optx = $tx;
				}

				$tj .= $sipset
					? '>] TJ '
					: ')] TJ ';

				if ($fontid != $last_fontid || $fontsize != $last_fontsize) {
					$tj .= sprintf(' /F%d %.3F Tf ', $fontid, $fontsize);
				}

				if ($fontstretch != $last_fontstretch) {
					$tj .= sprintf('%d Tz ', $fontstretch);
				}

				$tj .= sprintf('%.3F Ts ', $YPlacement);

				$tj .= $sipset
					? '[<'
					: '[(';

				// Output the code for the txt character(s)
				$tj .= $optx;
				$last_fontid = $fontid;
				$last_fontstretch = $fontstretch;
				$fontstretch = 100;
			}

			$lastYPlacement = $YPlacement;
		}

		$tj .= $sipset
			? '>'
			: ')';

		if ($XshiftAfter) {
			$tj .= sprintf('%d', (-$XshiftAfter));
		}

		if ($last_fontid != $original_fontid) {
			$tj .= '] TJ ';
			$tj .= sprintf(' /F%d %.3F Tf ', $original_fontid, $fontsize);
			$tj .= '[';
		}

		$tj = $sipset
			? preg_replace('/([^\\\])<>/', '\\1 ', $tj)
			: preg_replace('/([^\\\])\(\)/', '\\1 ', $tj);

		return sprintf(' BT ' . $aix . ' 0 Tc 0 Tw [%s] TJ ET ', $x, $y, $tj);
	}

	function _kern($txt, $mode, $aix, $x, $y)
	{
		if ($mode === 'MBTw') { // Multibyte requiring word spacing

			$space = ' ';

			// Convert string to UTF-16BE without BOM
			$space = $this->writer->utf8ToUtf16BigEndian($space, false);
			$space = $this->writer->escape($space);

			$s = sprintf(' BT ' . $aix, $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE);
			$t = explode(' ', $txt);

			foreach ($t as $i => $iValue) {
				$tx = $iValue;

				$tj = '(';
				$unicode = $this->UTF8StringToArray($tx);

				foreach ($unicode as $ti => $tiValue) {

					if ($ti > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($ti - 1)]][$tiValue])) {
						$kern = -$this->CurrentFont['kerninfo'][$unicode[($ti - 1)]][$tiValue];
						$tj .= sprintf(')%d(', $kern);
					}

					$tc = UtfString::code2utf($tiValue);
					$tc = $this->writer->utf8ToUtf16BigEndian($tc, false);
					$tj .= $this->writer->escape($tc);
				}

				$tj .= ')';
				$s .= sprintf(' %.3F Tc [%s] TJ', $this->charspacing, $tj);

				if (($i + 1) < count($t)) {
					$s .= sprintf(' %.3F Tc (%s) Tj', $this->ws + $this->charspacing, $space);
				}
			}

			$s .= ' ET ';

			return $s;

		}

		if (!$this->usingCoreFont) {

			$s = '';
			$tj = '(';

			$unicode = $this->UTF8StringToArray($txt);

			foreach ($unicode as $i => $iValue) {

				if ($i > 0 && isset($this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$iValue])) {
					$kern = -$this->CurrentFont['kerninfo'][$unicode[($i - 1)]][$iValue];
					$tj .= sprintf(')%d(', $kern);
				}

				$tx = UtfString::code2utf($iValue);
				$tx = $this->writer->utf8ToUtf16BigEndian($tx, false);
				$tj .= $this->writer->escape($tx);

			}

			$tj .= ')';
			$s .= sprintf(' BT ' . $aix . ' [%s] TJ ET ', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $tj);

			return $s;

		}

		$s = '';
		$tj = '(';
		$l = strlen($txt);

		for ($i = 0; $i < $l; $i++) {

			if ($i > 0 && isset($this->CurrentFont['kerninfo'][$txt[($i - 1)]][$txt[$i]])) {
				$kern = -$this->CurrentFont['kerninfo'][$txt[($i - 1)]][$txt[$i]];
				$tj .= sprintf(')%d(', $kern);
			}

			$tj .= $this->writer->escape($txt[$i]);
		}

		$tj .= ')';
		$s .= sprintf(' BT ' . $aix . ' [%s] TJ ET ', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $tj);

		return $s;
	}

	function MultiCell(
		$w,
		$h,
		$txt,
		$border = 0,
		$align = '',
		$fill = 0,
		$link = '',
		$directionality = 'ltr',
		$encoded = false,
		$OTLdata = false,
		$maxrows = false
	) {
		// maxrows is called from mpdfform->TEXTAREA
		// Parameter (pre-)encoded - When called internally from form::textarea -
		// mb_encoding already done and OTL - but not reverse RTL
		if (!$encoded) {

			$txt = $this->purify_utf8_text($txt);

			if ($this->text_input_as_HTML) {
				$txt = $this->all_entities_to_utf8($txt);
			}

			if ($this->usingCoreFont) {
				$txt = mb_convert_encoding($txt, $this->mb_enc, 'UTF-8');
			}

			if (preg_match("/([" . $this->pregRTLchars . "])/u", $txt)) {
				$this->biDirectional = true;
			}

			/* -- OTL -- */
			if (!is_array($OTLdata)) {
				unset($OTLdata);
			}

			// Use OTL OpenType Table Layout - GSUB & GPOS
			if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
				$txt = $this->otl->applyOTL($txt, $this->CurrentFont['useOTL']);
				$OTLdata = $this->otl->OTLdata;
			}

			if ($directionality == 'rtl' || $this->biDirectional) {
				if (!isset($OTLdata)) {
					$unicode = $this->UTF8StringToArray($txt, false);
					$is_strong = false;
					$this->getBasicOTLdata($OTLdata, $unicode, $is_strong);
				}
			}
			/* -- END OTL -- */
		}

		if (!$align) {
			$align = $this->defaultAlign;
		}

		// Output text with automatic or explicit line breaks
		$cw = &$this->CurrentFont['cw'];

		if ($w == 0) {
			$w = $this->w - $this->rMargin - $this->x;
		}

		$wmax = ($w - ($this->cMarginL + $this->cMarginR));

		if ($this->usingCoreFont) {
			$s = str_replace("\r", '', $txt);
			$nb = strlen($s);
			while ($nb > 0 and $s[$nb - 1] == "\n") {
				$nb--;
			}
		} else {
			$s = str_replace("\r", '', $txt);
			$nb = mb_strlen($s, $this->mb_enc);
			while ($nb > 0 and mb_substr($s, $nb - 1, 1, $this->mb_enc) == "\n") {
				$nb--;
			}
		}

		$b = 0;

		if ($border) {

			if ($border == 1) {
				$border = 'LTRB';
				$b = 'LRT';
				$b2 = 'LR';
			} else {
				$b2 = '';
				if (is_int(strpos($border, 'L'))) {
					$b2 .= 'L';
				}
				if (is_int(strpos($border, 'R'))) {
					$b2 .= 'R';
				}
				$b = is_int(strpos($border, 'T')) ? $b2 . 'T' : $b2;
			}
		}

		$sep = -1;
		$i = 0;
		$j = 0;
		$l = 0;
		$ns = 0;
		$nl = 1;

		$rows = 0;
		$start_y = $this->y;

		if (!$this->usingCoreFont) {

			$inclCursive = false;

			if (preg_match("/([" . $this->pregCURSchars . "])/u", $s)) {
				$inclCursive = true;
			}

			while ($i < $nb) {

				// Get next character
				$c = mb_substr($s, $i, 1, $this->mb_enc);

				if ($c === "\n") { // Explicit line break

					// WORD SPACING
					$this->ResetSpacing();
					$tmp = rtrim(mb_substr($s, $j, $i - $j, $this->mb_enc));
					$tmpOTLdata = false;

					/* -- OTL -- */
					if (isset($OTLdata)) {
						$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $i - $j);
						$this->otl->trimOTLdata($tmpOTLdata, false, true);
						$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
					}
					/* -- END OTL -- */

					$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);

					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}

					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;

					if ($border and $nl == 2) {
						$b = $b2;
					}

					continue;
				}

				if ($c == " ") {
					$sep = $i;
					$ls = $l;
					$ns++;
				}

				$l += $this->GetCharWidthNonCore($c);

				if ($l > $wmax) {

					// Automatic line break
					if ($sep == -1) { // Only one word

						if ($i == $j) {
							$i++;
						}

						// WORD SPACING
						$this->ResetSpacing();
						$tmp = rtrim(mb_substr($s, $j, $i - $j, $this->mb_enc));
						$tmpOTLdata = false;

						/* -- OTL -- */
						if (isset($OTLdata)) {
							$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $i - $j);
							$this->otl->trimOTLdata($tmpOTLdata, false, true);
							$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
						}
						/* -- END OTL -- */

						$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);

					} else {

						$tmp = rtrim(mb_substr($s, $j, $sep - $j, $this->mb_enc));
						$tmpOTLdata = false;

						/* -- OTL -- */
						if (isset($OTLdata)) {
							$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $sep - $j);
							$this->otl->trimOTLdata($tmpOTLdata, false, true);
						}
						/* -- END OTL -- */

						if ($align === 'J') {

							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING UNICODE
							// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly

							$tmp = str_replace(chr(194) . chr(160), chr(32), $tmp);
							$len_ligne = $this->GetStringWidth($tmp, false, $tmpOTLdata);
							$nb_carac = mb_strlen($tmp, $this->mb_enc);
							$nb_spaces = mb_substr_count($tmp, ' ', $this->mb_enc);

							// Take off number of Marks
							// Use GPOS OTL

							if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'])) {
								if (isset($tmpOTLdata['group']) && $tmpOTLdata['group']) {
									$nb_carac -= substr_count($tmpOTLdata['group'], 'M');
								}
							}

							list($charspacing, $ws, $kashida) = $this->GetJspacing($nb_carac, $nb_spaces, ((($wmax) - $len_ligne) * Mpdf::SCALE), $inclCursive, $tmpOTLdata);
							$this->SetSpacing($charspacing, $ws);
						}

						if (isset($OTLdata)) {
							$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
						}

						$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);

						$i = $sep + 1;
					}

					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}

					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;

					if ($border and $nl == 2) {
						$b = $b2;
					}

				} else {
					$i++;
				}
			}

			// Last chunk
			// WORD SPACING

			$this->ResetSpacing();

		} else {

			while ($i < $nb) {

				// Get next character
				$c = $s[$i];
				if ($c === "\n") {

					// Explicit line break
					// WORD SPACING

					$this->ResetSpacing();
					$this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill, $link);

					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}

					$i++;
					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;

					if ($border and $nl == 2) {
						$b = $b2;
					}

					continue;
				}

				if ($c === ' ') {
					$sep = $i;
					$ls = $l;
					$ns++;
				}

				$l += $this->GetCharWidthCore($c);

				if ($l > $wmax) {

					// Automatic line break
					if ($sep == -1) {

						if ($i == $j) {
							$i++;
						}

						// WORD SPACING
						$this->ResetSpacing();
						$this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill, $link);

					} else {

						if ($align === 'J') {

							$tmp = rtrim(substr($s, $j, $sep - $j));

							// JUSTIFY J using Unicode fonts (Word spacing doesn't work)
							// WORD SPACING NON_UNICODE/CJK
							// Change NON_BREAKING SPACE to spaces so they are 'spaced' properly

							$tmp = str_replace(chr(160), chr(32), $tmp);
							$len_ligne = $this->GetStringWidth($tmp);
							$nb_carac = strlen($tmp);
							$nb_spaces = substr_count($tmp, ' ');
							$tmpOTLdata = [];

							list($charspacing, $ws, $kashida) = $this->GetJspacing($nb_carac, $nb_spaces, ((($wmax) - $len_ligne) * Mpdf::SCALE), false, $tmpOTLdata);
							$this->SetSpacing($charspacing, $ws);
						}

						$this->Cell($w, $h, substr($s, $j, $sep - $j), $b, 2, $align, $fill, $link);
						$i = $sep + 1;
					}

					if ($maxrows != false && isset($this->form) && ($this->y - $start_y) / $h > $maxrows) {
						return false;
					}

					$sep = -1;
					$j = $i;
					$l = 0;
					$ns = 0;
					$nl++;

					if ($border and $nl == 2) {
						$b = $b2;
					}

				} else {
					$i++;
				}
			}

			// Last chunk
			// WORD SPACING

			$this->ResetSpacing();
		}

		// Last chunk
		if ($border and is_int(strpos($border, 'B'))) {
			$b .= 'B';
		}

		if (!$this->usingCoreFont) {

			$tmp = rtrim(mb_substr($s, $j, $i - $j, $this->mb_enc));
			$tmpOTLdata = false;

			/* -- OTL -- */
			if (isset($OTLdata)) {
				$tmpOTLdata = $this->otl->sliceOTLdata($OTLdata, $j, $i - $j);
				$this->otl->trimOTLdata($tmpOTLdata, false, true);
				$this->magic_reverse_dir($tmp, $directionality, $tmpOTLdata);
			}
			/* -- END OTL -- */

			$this->Cell($w, $h, $tmp, $b, 2, $align, $fill, $link, 0, 0, 0, 'M', 0, false, $tmpOTLdata);
		} else {
			$this->Cell($w, $h, substr($s, $j, $i - $j), $b, 2, $align, $fill, $link);
		}

		$this->x = $this->lMargin;
	}

	/* -- DIRECTW -- */

	function Write($h, $txt, $currentx = 0, $link = '', $directionality = 'ltr', $align = '', $fill = 0)
	{
		if (empty($this->directWrite)) {
			$this->directWrite = new DirectWrite($this, $this->otl, $this->sizeConverter, $this->colorConverter);
		}

		$this->directWrite->Write($h, $txt, $currentx, $link, $directionality, $align, $fill);
	}

	/* -- END DIRECTW -- */


	/* -- HTML-CSS -- */

	function saveInlineProperties()
	{
		$saved = [];
		$saved['family'] = $this->FontFamily;
		$saved['style'] = $this->FontStyle;
		$saved['sizePt'] = $this->FontSizePt;
		$saved['size'] = $this->FontSize;
		$saved['HREF'] = $this->HREF;
		$saved['textvar'] = $this->textvar; // mPDF 5.7.1
		$saved['OTLtags'] = $this->OTLtags; // mPDF 5.7.1
		$saved['textshadow'] = $this->textshadow;
		$saved['linewidth'] = $this->LineWidth;
		$saved['drawcolor'] = $this->DrawColor;
		$saved['textparam'] = $this->textparam;
		$saved['lSpacingCSS'] = $this->lSpacingCSS;
		$saved['wSpacingCSS'] = $this->wSpacingCSS;
		$saved['I'] = $this->I;
		$saved['B'] = $this->B;
		$saved['colorarray'] = $this->colorarray;
		$saved['bgcolorarray'] = $this->spanbgcolorarray;
		$saved['border'] = $this->spanborddet;
		$saved['color'] = $this->TextColor;
		$saved['bgcolor'] = $this->FillColor;
		$saved['lang'] = $this->currentLang;
		$saved['fontLanguageOverride'] = $this->fontLanguageOverride; // mPDF 5.7.1
		$saved['display_off'] = $this->inlineDisplayOff;

		return $saved;
	}

	function restoreInlineProperties(&$saved)
	{
		$FontFamily = $saved['family'];
		$this->FontStyle = $saved['style'];
		$this->FontSizePt = $saved['sizePt'];
		$this->FontSize = $saved['size'];

		$this->currentLang = $saved['lang'];
		$this->fontLanguageOverride = $saved['fontLanguageOverride']; // mPDF 5.7.1

		$this->ColorFlag = ($this->FillColor != $this->TextColor); // Restore ColorFlag as well

		$this->HREF = $saved['HREF'];
		$this->textvar = $saved['textvar']; // mPDF 5.7.1
		$this->OTLtags = $saved['OTLtags']; // mPDF 5.7.1
		$this->textshadow = $saved['textshadow'];
		$this->LineWidth = $saved['linewidth'];
		$this->DrawColor = $saved['drawcolor'];
		$this->textparam = $saved['textparam'];
		$this->inlineDisplayOff = $saved['display_off'];

		$this->lSpacingCSS = $saved['lSpacingCSS'];
		if (($this->lSpacingCSS || $this->lSpacingCSS === '0') && strtoupper($this->lSpacingCSS) != 'NORMAL') {
			$this->fixedlSpacing = $this->sizeConverter->convert($this->lSpacingCSS, $this->FontSize);
		} else {
			$this->fixedlSpacing = false;
		}
		$this->wSpacingCSS = $saved['wSpacingCSS'];
		if ($this->wSpacingCSS && strtoupper($this->wSpacingCSS) != 'NORMAL') {
			$this->minwSpacing = $this->sizeConverter->convert($this->wSpacingCSS, $this->FontSize);
		} else {
			$this->minwSpacing = 0;
		}

		$this->SetFont($FontFamily, $saved['style'], $saved['sizePt'], false);

		$this->currentfontstyle = $saved['style'];
		$this->currentfontsize = $saved['sizePt'];
		$this->SetStylesArray(['B' => $saved['B'], 'I' => $saved['I']]); // mPDF 5.7.1

		$this->TextColor = $saved['color'];
		$this->FillColor = $saved['bgcolor'];
		$this->colorarray = $saved['colorarray'];
		$cor = $saved['colorarray'];
		if ($cor) {
			$this->SetTColor($cor);
		}
		$this->spanbgcolorarray = $saved['bgcolorarray'];
		$cor = $saved['bgcolorarray'];
		if ($cor) {
			$this->SetFColor($cor);
		}
		$this->spanborddet = $saved['border'];
	}

	// Used when ColActive for tables - updated to return first block with background fill OR borders
	function GetFirstBlockFill()
	{
		// Returns the first blocklevel that uses a bgcolor fill
		$startfill = 0;
		for ($i = 1; $i <= $this->blklvl; $i++) {
			if ($this->blk[$i]['bgcolor'] || $this->blk[$i]['border_left']['w'] || $this->blk[$i]['border_right']['w'] || $this->blk[$i]['border_top']['w'] || $this->blk[$i]['border_bottom']['w']) {
				$startfill = $i;
				break;
			}
		}
		return $startfill;
	}

	// -------------------------FLOWING BLOCK------------------------------------//
	// The following functions were originally written by Damon Kohler           //
	// --------------------------------------------------------------------------//

	function saveFont()
	{
		$saved = [];
		$saved['family'] = $this->FontFamily;
		$saved['style'] = $this->FontStyle;
		$saved['sizePt'] = $this->FontSizePt;
		$saved['size'] = $this->FontSize;
		$saved['curr'] = &$this->CurrentFont;
		$saved['lang'] = $this->currentLang; // mPDF 6
		$saved['color'] = $this->TextColor;
		$saved['spanbgcolor'] = $this->spanbgcolor;
		$saved['spanbgcolorarray'] = $this->spanbgcolorarray;
		$saved['bord'] = $this->spanborder;
		$saved['border'] = $this->spanborddet;
		$saved['HREF'] = $this->HREF;
		$saved['textvar'] = $this->textvar; // mPDF 5.7.1
		$saved['textshadow'] = $this->textshadow;
		$saved['linewidth'] = $this->LineWidth;
		$saved['drawcolor'] = $this->DrawColor;
		$saved['textparam'] = $this->textparam;
		$saved['ReqFontStyle'] = $this->ReqFontStyle;
		$saved['fixedlSpacing'] = $this->fixedlSpacing;
		$saved['minwSpacing'] = $this->minwSpacing;
		return $saved;
	}

	function restoreFont(&$saved, $write = true)
	{
		if (!isset($saved) || empty($saved)) {
			return;
		}

		$this->FontFamily = $saved['family'];
		$this->FontStyle = $saved['style'];
		$this->FontSizePt = $saved['sizePt'];
		$this->FontSize = $saved['size'];
		$this->CurrentFont = &$saved['curr'];
		$this->currentLang = $saved['lang']; // mPDF 6
		$this->TextColor = $saved['color'];
		$this->spanbgcolor = $saved['spanbgcolor'];
		$this->spanbgcolorarray = $saved['spanbgcolorarray'];
		$this->spanborder = $saved['bord'];
		$this->spanborddet = $saved['border'];
		$this->ColorFlag = ($this->FillColor != $this->TextColor); // Restore ColorFlag as well
		$this->HREF = $saved['HREF'];
		$this->fixedlSpacing = $saved['fixedlSpacing'];
		$this->minwSpacing = $saved['minwSpacing'];
		$this->textvar = $saved['textvar'];  // mPDF 5.7.1
		$this->textshadow = $saved['textshadow'];
		$this->LineWidth = $saved['linewidth'];
		$this->DrawColor = $saved['drawcolor'];
		$this->textparam = $saved['textparam'];
		if ($write) {
			$this->SetFont($saved['family'], $saved['style'], $saved['sizePt'], true, true); // force output
			$fontout = (sprintf('BT /F%d %.3F Tf ET', $this->CurrentFont['i'], $this->FontSizePt));
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Font']) && $this->pageoutput[$this->page]['Font'] != $fontout) || !isset($this->pageoutput[$this->page]['Font']))) {
				$this->writer->write($fontout);
			}
			$this->pageoutput[$this->page]['Font'] = $fontout;
		} else {
			$this->SetFont($saved['family'], $saved['style'], $saved['sizePt'], false);
		}
		$this->ReqFontStyle = $saved['ReqFontStyle'];
	}

	function newFlowingBlock($w, $h, $a = '', $is_table = false, $blockstate = 0, $newblock = true, $blockdir = 'ltr', $table_draft = false)
	{
		if (!$a) {
			if ($blockdir == 'rtl') {
				$a = 'R';
			} else {
				$a = 'L';
			}
		}
		$this->flowingBlockAttr['width'] = ($w * Mpdf::SCALE);
		// line height in user units
		$this->flowingBlockAttr['is_table'] = $is_table;
		$this->flowingBlockAttr['table_draft'] = $table_draft;
		$this->flowingBlockAttr['height'] = $h;
		$this->flowingBlockAttr['lineCount'] = 0;
		$this->flowingBlockAttr['align'] = $a;
		$this->flowingBlockAttr['font'] = [];
		$this->flowingBlockAttr['content'] = [];
		$this->flowingBlockAttr['contentB'] = [];
		$this->flowingBlockAttr['contentWidth'] = 0;
		$this->flowingBlockAttr['blockstate'] = $blockstate;

		$this->flowingBlockAttr['newblock'] = $newblock;
		$this->flowingBlockAttr['valign'] = 'M';
		$this->flowingBlockAttr['blockdir'] = $blockdir;
		$this->flowingBlockAttr['cOTLdata'] = []; // mPDF 5.7.1
		$this->flowingBlockAttr['lastBidiText'] = ''; // mPDF 5.7.1
		if (!empty($this->otl)) {
			$this->otl->lastBidiStrongType = '';
		} // *OTL*
	}

	function finishFlowingBlock($endofblock = false, $next = '')
	{
		$currentx = $this->x;
		// prints out the last chunk
		$is_table = $this->flowingBlockAttr['is_table'];
		$table_draft = $this->flowingBlockAttr['table_draft'];
		$maxWidth = & $this->flowingBlockAttr['width'];
		$stackHeight = & $this->flowingBlockAttr['height'];
		$align = & $this->flowingBlockAttr['align'];
		$content = & $this->flowingBlockAttr['content'];
		$contentB = & $this->flowingBlockAttr['contentB'];
		$font = & $this->flowingBlockAttr['font'];
		$contentWidth = & $this->flowingBlockAttr['contentWidth'];
		$lineCount = & $this->flowingBlockAttr['lineCount'];
		$valign = & $this->flowingBlockAttr['valign'];
		$blockstate = $this->flowingBlockAttr['blockstate'];

		$cOTLdata = & $this->flowingBlockAttr['cOTLdata']; // mPDF 5.7.1
		$newblock = $this->flowingBlockAttr['newblock'];
		$blockdir = $this->flowingBlockAttr['blockdir'];

		// *********** BLOCK BACKGROUND COLOR *****************//
		if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
			$fill = 0;
		} else {
			$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
			$fill = 0;
		}

		$hanger = '';
		// Always right trim!
		// Right trim last content and adjust width if needed to justify (later)
		if (isset($content[count($content) - 1]) && preg_match('/[ ]+$/', $content[count($content) - 1], $m)) {
			$strip = strlen($m[0]);
			$content[count($content) - 1] = substr($content[count($content) - 1], 0, (strlen($content[count($content) - 1]) - $strip));
			/* -- OTL -- */
			if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
				$this->otl->trimOTLdata($cOTLdata[count($cOTLdata) - 1], false, true);
			}
			/* -- END OTL -- */
		}

		// the amount of space taken up so far in user units
		$usedWidth = 0;

		// COLS
		$oldcolumn = $this->CurrCol;

		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		} // *COLUMNS*
		// Print out each chunk

		/* -- TABLES -- */
		if ($is_table) {
			$ipaddingL = 0;
			$ipaddingR = 0;
			$paddingL = 0;
			$paddingR = 0;
		} else {
			/* -- END TABLES -- */
			$ipaddingL = $this->blk[$this->blklvl]['padding_left'];
			$ipaddingR = $this->blk[$this->blklvl]['padding_right'];
			$paddingL = ($ipaddingL * Mpdf::SCALE);
			$paddingR = ($ipaddingR * Mpdf::SCALE);
			$this->cMarginL = $this->blk[$this->blklvl]['border_left']['w'];
			$this->cMarginR = $this->blk[$this->blklvl]['border_right']['w'];

			// Added mPDF 3.0 Float DIV
			$fpaddingR = 0;
			$fpaddingL = 0;
			/* -- CSS-FLOAT -- */
			if (count($this->floatDivs)) {
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
				if ($r_exists) {
					$fpaddingR = $r_width;
				}
				if ($l_exists) {
					$fpaddingL = $l_width;
				}
			}
			/* -- END CSS-FLOAT -- */

			$usey = $this->y + 0.002;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
				$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
			}
			/* -- CSS-IMAGE-FLOAT -- */
			// If float exists at this level
			if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
				$fpaddingR += $this->floatmargins['R']['w'];
			}
			if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
				$fpaddingL += $this->floatmargins['L']['w'];
			}
			/* -- END CSS-IMAGE-FLOAT -- */
		} // *TABLES*


		$lineBox = [];

		$this->_setInlineBlockHeights($lineBox, $stackHeight, $content, $font, $is_table);

		if ($is_table && count($content) == 0) {
			$stackHeight = 0;
		}

		if ($table_draft) {
			$this->y += $stackHeight;
			$this->objectbuffer = [];
			return 0;
		}

		// While we're at it, check if contains cursive text
		// Change NBSP to SPACE.
		// Re-calculate contentWidth
		$contentWidth = 0;

		foreach ($content as $k => $chunk) {
			$this->restoreFont($font[$k], false);
			if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
				// Soft Hyphens chr(173)
				if (!$this->usingCoreFont) {
					/* -- OTL -- */
					// mPDF 5.7.1
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
						$this->otl->removeChar($chunk, $cOTLdata[$k], "\xc2\xad");
						$this->otl->replaceSpace($chunk, $cOTLdata[$k]);
						$content[$k] = $chunk;
					} /* -- END OTL -- */ else {  // *OTL*
						$content[$k] = $chunk = str_replace("\xc2\xad", '', $chunk);
						$content[$k] = $chunk = str_replace(chr(194) . chr(160), chr(32), $chunk);
					} // *OTL*
				} elseif ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
					$content[$k] = $chunk = str_replace(chr(173), '', $chunk);
					$content[$k] = $chunk = str_replace(chr(160), chr(32), $chunk);
				}
				$contentWidth += $this->GetStringWidth($chunk, true, (isset($cOTLdata[$k]) ? $cOTLdata[$k] : false), $this->textvar) * Mpdf::SCALE;
			} elseif (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
				// LIST MARKERS	// mPDF 6  Lists
				if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker'] && $this->objectbuffer[$k]['listmarkerposition'] == 'outside') {
					// do nothing
				} else {
					$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * Mpdf::SCALE;
				}
			}
		}

		if (isset($font[count($font) - 1])) {
			$lastfontreqstyle = (isset($font[count($font) - 1]['ReqFontStyle']) ? $font[count($font) - 1]['ReqFontStyle'] : '');
			$lastfontstyle = (isset($font[count($font) - 1]['style']) ? $font[count($font) - 1]['style'] : '');
		} else {
			$lastfontreqstyle = null;
			$lastfontstyle = null;
		}
		if ($blockdir == 'ltr' && $lastfontreqstyle && strpos($lastfontreqstyle, "I") !== false && strpos($lastfontstyle, "I") === false) { // Artificial italic
			$lastitalic = $this->FontSize * 0.15 * Mpdf::SCALE;
		} else {
			$lastitalic = 0;
		}

		// Get PAGEBREAK TO TEST for height including the bottom border/padding
		$check_h = max($this->divheight, $stackHeight);

		// This fixes a proven bug...
		if ($endofblock && $newblock && $blockstate == 0 && !$content) {
			$check_h = 0;
		}
		// but ? needs to fix potentially more widespread...
		// if (!$content) {  $check_h = 0; }

		if ($this->blklvl > 0 && !$is_table) {
			if ($endofblock && $blockstate > 1) {
				if ($this->blk[$this->blklvl]['page_break_after_avoid']) {
					$check_h += $stackHeight;
				}
				$check_h += ($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
			}
			if (($newblock && ($blockstate == 1 || $blockstate == 3) && $lineCount == 0) || ($endofblock && $blockstate == 3 && $lineCount == 0)) {
				$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
			}
		}

		// Force PAGE break if column height cannot take check-height
		if ($this->ColActive && $check_h > ($this->PageBreakTrigger - $this->y0)) {
			$this->SetCol($this->NbCol - 1);
		}

		// Avoid just border/background-color moved on to next page
		if ($endofblock && $blockstate > 1 && !$content) {
			$buff = $this->margBuffer;
		} else {
			$buff = 0;
		}


		// PAGEBREAK
		if (!$is_table && ($this->y + $check_h) > ($this->PageBreakTrigger + $buff) and ! $this->InFooter and $this->AcceptPageBreak()) {
			$bak_x = $this->x; // Current X position
			// WORD SPACING
			$ws = $this->ws; // Word Spacing
			$charspacing = $this->charspacing; // Character Spacing
			$this->ResetSpacing();

			$this->AddPage($this->CurOrientation);

			$this->x = $bak_x;
			// Added to correct for OddEven Margins
			$currentx += $this->MarginCorrection;
			$this->x += $this->MarginCorrection;

			// WORD SPACING
			$this->SetSpacing($charspacing, $ws);
		}


		/* -- COLUMNS -- */
		// COLS
		// COLUMN CHANGE
		if ($this->CurrCol != $oldcolumn) {
			$currentx += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			$this->x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			$oldcolumn = $this->CurrCol;
		}


		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		}
		/* -- END COLUMNS -- */

		// TOP MARGIN
		if ($newblock && ($blockstate == 1 || $blockstate == 3) && ($this->blk[$this->blklvl]['margin_top']) && $lineCount == 0 && !$is_table) {
			$this->DivLn($this->blk[$this->blklvl]['margin_top'], $this->blklvl - 1, true, $this->blk[$this->blklvl]['margin_collapse']);
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		if ($newblock && ($blockstate == 1 || $blockstate == 3) && $lineCount == 0 && !$is_table) {
			$this->blk[$this->blklvl]['y0'] = $this->y;
			$this->blk[$this->blklvl]['startpage'] = $this->page;
			if ($this->blk[$this->blklvl]['float']) {
				$this->blk[$this->blklvl]['float_start_y'] = $this->y;
			}
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		// Paragraph INDENT
		$WidthCorrection = 0;
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && ($align != 'C')) {
			$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
			$WidthCorrection = ($ti * Mpdf::SCALE);
		}


		// PADDING and BORDER spacing/fill
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 0) && (!$is_table)) {
			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'], -3, true, false, 1);
			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
			$this->x = $currentx;
		}


		// Added mPDF 3.0 Float DIV
		$fpaddingR = 0;
		$fpaddingL = 0;
		/* -- CSS-FLOAT -- */
		if (count($this->floatDivs)) {
			list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
			if ($r_exists) {
				$fpaddingR = $r_width;
			}
			if ($l_exists) {
				$fpaddingL = $l_width;
			}
		}
		/* -- END CSS-FLOAT -- */

		$usey = $this->y + 0.002;
		if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
			$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
		}
		/* -- CSS-IMAGE-FLOAT -- */
		// If float exists at this level
		if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
			$fpaddingR += $this->floatmargins['R']['w'];
		}
		if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
			$fpaddingL += $this->floatmargins['L']['w'];
		}
		/* -- END CSS-IMAGE-FLOAT -- */


		if ($content) {
			// In FinishFlowing Block no lines are justified as it is always last line
			// but if CJKorphan has allowed content width to go over max width, use J charspacing to compress line
			// JUSTIFICATION J - NOT!
			$nb_carac = 0;
			$nb_spaces = 0;
			$jcharspacing = 0;
			$jkashida = 0;
			$jws = 0;
			$inclCursive = false;
			$dottab = false;
			foreach ($content as $k => $chunk) {
				if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
					$nb_carac += mb_strlen($chunk, $this->mb_enc);
					$nb_spaces += mb_substr_count($chunk, ' ', $this->mb_enc);
					// mPDF 6
					// Use GPOS OTL
					$this->restoreFont($font[$k], false);
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
						if (isset($cOTLdata[$k]['group']) && $cOTLdata[$k]['group']) {
							$nb_marks = substr_count($cOTLdata[$k]['group'], 'M');
							$nb_carac -= $nb_marks;
						}
						if (preg_match("/([" . $this->pregCURSchars . "])/u", $chunk)) {
							$inclCursive = true;
						}
					}
				} else {
					$nb_carac ++;  // mPDF 6 allow spacing for inline object
					if ($this->objectbuffer[$k]['type'] == 'dottab') {
						$dottab = $this->objectbuffer[$k]['outdent'];
					}
				}
			}

			// DIRECTIONALITY RTL
			$chunkorder = range(0, count($content) - 1); // mPDF 6
			/* -- OTL -- */
			// mPDF 6
			if ($blockdir == 'rtl' || $this->biDirectional) {
				$this->otl->bidiReorder($chunkorder, $content, $cOTLdata, $blockdir);
				// From this point on, $content and $cOTLdata may contain more elements (and re-ordered) compared to
				// $this->objectbuffer and $font ($chunkorder contains the mapping)
			}
			/* -- END OTL -- */

			// Remove any XAdvance from OTL data at end of line
			// And correct for XPlacement on last character
			// BIDI is applied
			foreach ($chunkorder as $aord => $k) {
				if (count($cOTLdata)) {
					$this->restoreFont($font[$k], false);
					// ...FinishFlowingBlock...
					if ($aord == count($chunkorder) - 1 && isset($cOTLdata[$aord]['group'])) { // Last chunk on line
						$nGPOS = strlen($cOTLdata[$aord]['group']) - 1; // Last character
						if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL']) || isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'])) {
							if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'])) {
								$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
							} else {
								$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
							}
							$w *= ($this->FontSize / 1000);
							$contentWidth -= $w * Mpdf::SCALE;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = 0;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = 0;
						}

						// If last character has an XPlacement set, adjust width calculation, and add to XAdvance to account for it
						if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'])) {
							$w = -$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
							$w *= ($this->FontSize / 1000);
							$contentWidth -= $w * Mpdf::SCALE;
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
							$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
						}
					}
				}
			}

			// if it's justified, we need to find the char/word spacing (or if orphans have allowed length of line to go over the maxwidth)
			// If "orphans" in fact is just a final space - ignore this
			$lastchar = mb_substr($content[(count($chunkorder) - 1)], mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, 1, $this->mb_enc);
			if (preg_match("/[" . $this->CJKoverflow . "]/u", $lastchar)) {
				$CJKoverflow = true;
			} else {
				$CJKoverflow = false;
			}
			if ((((($contentWidth + $lastitalic) > $maxWidth) && ($content[(count($chunkorder) - 1)] != ' ') ) ||
				(!$endofblock && $align == 'J' && ($next == 'image' || $next == 'select' || $next == 'input' || $next == 'textarea' || ($next == 'br' && $this->justifyB4br)))) && !($CJKoverflow && $this->allowCJKoverflow)) {
				// WORD SPACING
				list($jcharspacing, $jws, $jkashida) = $this->GetJspacing($nb_carac, $nb_spaces, ($maxWidth - $lastitalic - $contentWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) )), $inclCursive, $cOTLdata);
			} /* -- CJK-FONTS -- */ elseif ($this->checkCJK && $align == 'J' && $CJKoverflow && $this->allowCJKoverflow && $this->CJKforceend) {
				// force-end overhang
				$hanger = mb_substr($content[(count($chunkorder) - 1)], mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, 1, $this->mb_enc);
				if (preg_match("/[" . $this->CJKoverflow . "]/u", $hanger)) {
					$content[(count($chunkorder) - 1)] = mb_substr($content[(count($chunkorder) - 1)], 0, mb_strlen($content[(count($chunkorder) - 1)], $this->mb_enc) - 1, $this->mb_enc);
					$this->restoreFont($font[$chunkorder[count($chunkorder) - 1]], false);
					$contentWidth -= $this->GetStringWidth($hanger) * Mpdf::SCALE;
					$nb_carac -= 1;
					list($jcharspacing, $jws, $jkashida) = $this->GetJspacing($nb_carac, $nb_spaces, ($maxWidth - $lastitalic - $contentWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) )), $inclCursive, $cOTLdata);
				}
			} /* -- END CJK-FONTS -- */

			// Check if will fit at word/char spacing of previous line - if so continue it
			// but only allow a maximum of $this->jSmaxWordLast and $this->jSmaxCharLast
			elseif ($contentWidth < ($maxWidth - $lastitalic - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE))) && !$this->fixedlSpacing) {
				if ($this->ws > $this->jSmaxWordLast) {
					$jws = $this->jSmaxWordLast;
				}
				if ($this->charspacing > $this->jSmaxCharLast) {
					$jcharspacing = $this->jSmaxCharLast;
				}
				$check = $maxWidth - $lastitalic - $WidthCorrection - $contentWidth - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) ) - ( $jcharspacing * $nb_carac) - ( $jws * $nb_spaces);
				if ($check <= 0) {
					$jcharspacing = 0;
					$jws = 0;
				}
			}

			$empty = $maxWidth - $lastitalic - $WidthCorrection - $contentWidth - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) );


			$empty -= ($jcharspacing * ($nb_carac - 1)); // mPDF 6 nb_carac MINUS 1
			$empty -= ($jws * $nb_spaces);
			$empty -= ($jkashida);

			$empty /= Mpdf::SCALE;

			if (!$is_table) {
				$this->maxPosR = max($this->maxPosR, ($this->w - $this->rMargin - $this->blk[$this->blklvl]['outer_right_margin'] - $empty));
				$this->maxPosL = min($this->maxPosL, ($this->lMargin + $this->blk[$this->blklvl]['outer_left_margin'] + $empty));
			}

			$arraysize = count($chunkorder);

			$margins = ($this->cMarginL + $this->cMarginR) + ($ipaddingL + $ipaddingR + $fpaddingR + $fpaddingR );

			if (!$is_table) {
				$this->DivLn($stackHeight, $this->blklvl, false);
			} // false -> don't advance y

			$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL;
			if ($dottab !== false && $blockdir == 'rtl') {
				$this->x -= $dottab;
			} elseif ($align == 'R') {
				$this->x += $empty;
			} elseif ($align == 'J' && $blockdir == 'rtl') {
				$this->x += $empty;
			} elseif ($align == 'C') {
				$this->x += ($empty / 2);
			}

			// Paragraph INDENT
			$WidthCorrection = 0;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && ($align != 'C')) {
				$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
				if ($blockdir != 'rtl') {
					$this->x += $ti;
				} // mPDF 6
			}

			foreach ($chunkorder as $aord => $k) { // mPDF 5.7
				$chunk = $content[$aord];
				if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
					$xadj = $this->x - $this->objectbuffer[$k]['OUTER-X'];
					$this->objectbuffer[$k]['OUTER-X'] += $xadj;
					$this->objectbuffer[$k]['BORDER-X'] += $xadj;
					$this->objectbuffer[$k]['INNER-X'] += $xadj;

					if ($this->objectbuffer[$k]['type'] == 'listmarker') {
						$this->objectbuffer[$k]['lineBox'] = $lineBox[-1]; // Block element details for glyph-origin
					}
					$yadj = $this->y - $this->objectbuffer[$k]['OUTER-Y'];
					if ($this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
						$this->objectbuffer[$k]['lineBox'] = $lineBox[$k]; // element details for glyph-origin
					}
					if ($this->objectbuffer[$k]['type'] != 'dottab') { // mPDF 6 DOTTAB
						$yadj += $lineBox[$k]['top'];
					}
					$this->objectbuffer[$k]['OUTER-Y'] += $yadj;
					$this->objectbuffer[$k]['BORDER-Y'] += $yadj;
					$this->objectbuffer[$k]['INNER-Y'] += $yadj;
				}

				$this->restoreFont($font[$k]);  // mPDF 5.7

				if ($is_table && substr($align, 0, 1) == 'D' && $aord == 0) {
					$dp = $this->decimal_align[substr($align, 0, 2)];
					$s = preg_split('/' . preg_quote($dp, '/') . '/', $content[0], 2);  // ? needs to be /u if not core
					$s0 = $this->GetStringWidth($s[0], false);
					$this->x += ($this->decimal_offset - $s0);
				}

				$this->SetSpacing(($this->fixedlSpacing * Mpdf::SCALE) + $jcharspacing, ($this->fixedlSpacing + $this->minwSpacing) * Mpdf::SCALE + $jws);
				$this->fixedlSpacing = false;
				$this->minwSpacing = 0;

				$save_vis = $this->visibility;
				if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->textparam['visibility'] != $this->visibility) {
					$this->SetVisibility($this->textparam['visibility']);
				}

				// *********** SPAN BACKGROUND COLOR ***************** //
				if (isset($this->spanbgcolor) && $this->spanbgcolor) {
					$cor = $this->spanbgcolorarray;
					$this->SetFColor($cor);
					$save_fill = $fill;
					$spanfill = 1;
					$fill = 1;
				}
				if (!empty($this->spanborddet)) {
					if (strpos($contentB[$k], 'L') !== false && isset($this->spanborddet['L'])) {
						$this->x += $this->spanborddet['L']['w'];
					}
					if (strpos($contentB[$k], 'L') === false) {
						$this->spanborddet['L']['s'] = $this->spanborddet['L']['w'] = 0;
					}
					if (strpos($contentB[$k], 'R') === false) {
						$this->spanborddet['R']['s'] = $this->spanborddet['R']['w'] = 0;
					}
				}
				// WORD SPACING
				// mPDF 5.7.1
				$stringWidth = $this->GetStringWidth($chunk, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar);
				$nch = mb_strlen($chunk, $this->mb_enc);
				// Use GPOS OTL
				if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
					if (isset($cOTLdata[$aord]['group']) && $cOTLdata[$aord]['group']) {
						$nch -= substr_count($cOTLdata[$aord]['group'], 'M');
					}
				}
				$stringWidth += ( $this->charspacing * $nch / Mpdf::SCALE );

				$stringWidth += ( $this->ws * mb_substr_count($chunk, ' ', $this->mb_enc) / Mpdf::SCALE );

				if (isset($this->objectbuffer[$k])) {
					if ($this->objectbuffer[$k]['type'] == 'dottab') {
						$this->objectbuffer[$k]['OUTER-WIDTH'] +=$empty;
						$this->objectbuffer[$k]['OUTER-WIDTH'] +=$this->objectbuffer[$k]['outdent'];
					}
					// LIST MARKERS	// mPDF 6  Lists
					if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker'] && $this->objectbuffer[$k]['listmarkerposition'] == 'outside') {
						// do nothing
					} else {
						$stringWidth = $this->objectbuffer[$k]['OUTER-WIDTH'];
					}
				}

				if ($stringWidth == 0) {
					$stringWidth = 0.000001;
				}
				if ($aord == $arraysize - 1) { // mPDF 5.7
					// mPDF 5.7.1
					if ($this->checkCJK && $CJKoverflow && $align == 'J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {
						// force-end overhang
						$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false));  // mPDF 5.7.1
						$this->Cell($this->GetStringWidth($hanger), $stackHeight, $hanger, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // mPDF 5.7.1
					} else {
						$this->Cell($stringWidth, $stackHeight, $chunk, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // mPDF 5.7.1
					}
				} else {
					$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // first or middle part	// mPDF 5.7.1
				}


				if (!empty($this->spanborddet)) {
					if (strpos($contentB[$k], 'R') !== false && $aord != $arraysize - 1) {
						$this->x += $this->spanborddet['R']['w'];
					}
				}
				// *********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR ***************** //
				if (isset($spanfill) && $spanfill) {
					$fill = $save_fill;
					$spanfill = 0;
					if ($fill) {
						$this->SetFColor($bcor);
					}
				}
				if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->visibility != $save_vis) {
					$this->SetVisibility($save_vis);
				}
			}

			$this->printobjectbuffer($is_table, $blockdir);
			$this->objectbuffer = [];
			$this->ResetSpacing();
		} // END IF CONTENT

		/* -- CSS-IMAGE-FLOAT -- */
		// Update values if set to skipline
		if ($this->floatmargins) {
			$this->_advanceFloatMargins();
		}


		if ($endofblock && $blockstate > 1) {
			// If float exists at this level
			if (isset($this->floatmargins['R']['y1'])) {
				$fry1 = $this->floatmargins['R']['y1'];
			} else {
				$fry1 = 0;
			}
			if (isset($this->floatmargins['L']['y1'])) {
				$fly1 = $this->floatmargins['L']['y1'];
			} else {
				$fly1 = 0;
			}
			if ($this->y < $fry1 || $this->y < $fly1) {
				$drop = max($fry1, $fly1) - $this->y;
				$this->DivLn($drop);
				$this->x = $currentx;
			}
		}
		/* -- END CSS-IMAGE-FLOAT -- */


		// PADDING and BORDER spacing/fill
		if ($endofblock && ($blockstate > 1) && ($this->blk[$this->blklvl]['padding_bottom'] || $this->blk[$this->blklvl]['border_bottom'] || $this->blk[$this->blklvl]['css_set_height']) && (!$is_table)) {
			// If CSS height set, extend bottom - if on same page as block started, and CSS HEIGHT > actual height,
			// and does not force pagebreak
			$extra = 0;
			if (isset($this->blk[$this->blklvl]['css_set_height']) && $this->blk[$this->blklvl]['css_set_height'] && $this->blk[$this->blklvl]['startpage'] == $this->page) {
				// predicted height
				$h1 = ($this->y - $this->blk[$this->blklvl]['y0']) + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'];
				if ($h1 < ($this->blk[$this->blklvl]['css_set_height'] + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['padding_top'])) {
					$extra = ($this->blk[$this->blklvl]['css_set_height'] + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['padding_top']) - $h1;
				}
				if ($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra > $this->PageBreakTrigger) {
					$extra = $this->PageBreakTrigger - ($this->y + $this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w']);
				}
			}

			// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
			$this->DivLn($this->blk[$this->blklvl]['padding_bottom'] + $this->blk[$this->blklvl]['border_bottom']['w'] + $extra, -3, true, false, 2);
			$this->x = $currentx;

			if ($this->ColActive) {
				$this->breakpoints[$this->CurrCol][] = $this->y;
			} // *COLUMNS*
		}

		// SET Bottom y1 of block (used for painting borders)
		if (($endofblock) && ($blockstate > 1) && (!$is_table)) {
			$this->blk[$this->blklvl]['y1'] = $this->y;
		}

		// BOTTOM MARGIN
		if (($endofblock) && ($blockstate > 1) && ($this->blk[$this->blklvl]['margin_bottom']) && (!$is_table)) {
			if ($this->y + $this->blk[$this->blklvl]['margin_bottom'] < $this->PageBreakTrigger and ! $this->InFooter) {
				$this->DivLn($this->blk[$this->blklvl]['margin_bottom'], $this->blklvl - 1, true, $this->blk[$this->blklvl]['margin_collapse']);
				if ($this->ColActive) {
					$this->breakpoints[$this->CurrCol][] = $this->y;
				} // *COLUMNS*
			}
		}

		// Reset lineheight
		$stackHeight = $this->divheight;
	}

	function printobjectbuffer($is_table = false, $blockdir = false)
	{
		if (!$blockdir) {
			$blockdir = $this->directionality;
		}

		if ($is_table && $this->shrin_k > 1) {
			$k = $this->shrin_k;
		} else {
			$k = 1;
		}

		$save_y = $this->y;
		$save_x = $this->x;

		$save_currentfontfamily = $this->FontFamily;
		$save_currentfontsize = $this->FontSizePt;
		$save_currentfontstyle = $this->FontStyle;

		if ($blockdir == 'rtl') {
			$rtlalign = 'R';
		} else {
			$rtlalign = 'L';
		}

		foreach ($this->objectbuffer as $ib => $objattr) {

			if ($objattr['type'] == 'bookmark' || $objattr['type'] == 'indexentry' || $objattr['type'] == 'toc') {
				$x = $objattr['OUTER-X'];
				$y = $objattr['OUTER-Y'];
				$this->y = $y - $this->FontSize / 2;
				$this->x = $x;
				if ($objattr['type'] == 'bookmark') {
					$this->Bookmark($objattr['CONTENT'], $objattr['bklevel'], $y - $this->FontSize);
				} // *BOOKMARKS*
				if ($objattr['type'] == 'indexentry') {
					$this->IndexEntry($objattr['CONTENT']);
				} // *INDEX*
				if ($objattr['type'] == 'toc') {
					$this->TOC_Entry($objattr['CONTENT'], $objattr['toclevel'], (isset($objattr['toc_id']) ? $objattr['toc_id'] : ''));
				} // *TOC*
			} /* -- ANNOTATIONS -- */ elseif ($objattr['type'] == 'annot') {
				if ($objattr['POS-X']) {
					$x = $objattr['POS-X'];
				} elseif ($this->annotMargin <> 0) {
					$x = -$objattr['OUTER-X'];
				} else {
					$x = $objattr['OUTER-X'];
				}
				if ($objattr['POS-Y']) {
					$y = $objattr['POS-Y'];
				} else {
					$y = $objattr['OUTER-Y'] - $this->FontSize / 2;
				}
				// Create a dummy entry in the _out/columnBuffer with position sensitive data,
				// linking $y-1 in the Columnbuffer with entry in $this->columnAnnots
				// and when columns are split in length will not break annotation from current line
				$this->y = $y - 1;
				$this->x = $x - 1;
				$this->Line($x - 1, $y - 1, $x - 1, $y - 1);
				$this->Annotation($objattr['CONTENT'], $x, $y, $objattr['ICON'], $objattr['AUTHOR'], $objattr['SUBJECT'], $objattr['OPACITY'], $objattr['COLOR'], (isset($objattr['POPUP']) ? $objattr['POPUP'] : ''), (isset($objattr['FILE']) ? $objattr['FILE'] : ''));
			} /* -- END ANNOTATIONS -- */ else {
				$y = $objattr['OUTER-Y'];
				$x = $objattr['OUTER-X'];
				$w = $objattr['OUTER-WIDTH'];
				$h = $objattr['OUTER-HEIGHT'];
				if (isset($objattr['text'])) {
					$texto = $objattr['text'];
				}
				$this->y = $y;
				$this->x = $x;
				if (isset($objattr['fontfamily'])) {
					$this->SetFont($objattr['fontfamily'], '', $objattr['fontsize']);
				}
			}

			// HR
			if ($objattr['type'] == 'hr') {
				$this->SetDColor($objattr['color']);
				switch ($objattr['align']) {
					case 'C':
						$empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
						$empty /= 2;
						$x += $empty;
						break;
					case 'R':
						$empty = $objattr['OUTER-WIDTH'] - $objattr['INNER-WIDTH'];
						$x += $empty;
						break;
				}
				$oldlinewidth = $this->LineWidth;
				$this->SetLineWidth($objattr['linewidth'] / $k);
				$this->y += ($objattr['linewidth'] / 2) + $objattr['margin_top'] / $k;
				$this->Line($x, $this->y, $x + $objattr['INNER-WIDTH'], $this->y);
				$this->SetLineWidth($oldlinewidth);
				$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			}
			// IMAGE
			if ($objattr['type'] == 'image') {
				// mPDF 5.7.3 TRANSFORMS
				if (isset($objattr['transform'])) {
					$this->writer->write("\n" . '% BTR'); // Begin Transform
				}
				if (isset($objattr['z-index']) && $objattr['z-index'] > 0 && $this->current_layer == 0) {
					$this->BeginLayer($objattr['z-index']);
				}
				if (isset($objattr['visibility']) && $objattr['visibility'] != 'visible' && $objattr['visibility']) {
					$this->SetVisibility($objattr['visibility']);
				}
				if (isset($objattr['opacity'])) {
					$this->SetAlpha($objattr['opacity']);
				}

				$obiw = $objattr['INNER-WIDTH'];
				$obih = $objattr['INNER-HEIGHT'];

				$sx = $objattr['orig_w'] ? ($objattr['INNER-WIDTH'] * Mpdf::SCALE / $objattr['orig_w']) : INF;
				$sy = $objattr['orig_h'] ? ($objattr['INNER-HEIGHT'] * Mpdf::SCALE / $objattr['orig_h']) : INF;

				$rotate = 0;
				if (isset($objattr['ROTATE'])) {
					$rotate = $objattr['ROTATE'];
				}

				if ($rotate == 90) {
					// Clockwise
					$obiw = $objattr['INNER-HEIGHT'];
					$obih = $objattr['INNER-WIDTH'];
					$tr = $this->transformTranslate(0, -$objattr['INNER-WIDTH'], true);
					$tr .= ' ' . $this->transformRotate(90, $objattr['INNER-X'], ($objattr['INNER-Y'] + $objattr['INNER-WIDTH']), true);
					$sx = $obiw * Mpdf::SCALE / $objattr['orig_h'];
					$sy = $obih * Mpdf::SCALE / $objattr['orig_w'];
				} elseif ($rotate == -90 || $rotate == 270) {
					// AntiClockwise
					$obiw = $objattr['INNER-HEIGHT'];
					$obih = $objattr['INNER-WIDTH'];
					$tr = $this->transformTranslate($objattr['INNER-WIDTH'], ($objattr['INNER-HEIGHT'] - $objattr['INNER-WIDTH']), true);
					$tr .= ' ' . $this->transformRotate(-90, $objattr['INNER-X'], ($objattr['INNER-Y'] + $objattr['INNER-WIDTH']), true);
					$sx = $obiw * Mpdf::SCALE / $objattr['orig_h'];
					$sy = $obih * Mpdf::SCALE / $objattr['orig_w'];
				} elseif ($rotate == 180) {
					// Mirror
					$tr = $this->transformTranslate($objattr['INNER-WIDTH'], -$objattr['INNER-HEIGHT'], true);
					$tr .= ' ' . $this->transformRotate(180, $objattr['INNER-X'], ($objattr['INNER-Y'] + $objattr['INNER-HEIGHT']), true);
				} else {
					$tr = '';
				}
				$tr = trim($tr);
				if ($tr) {
					$tr .= ' ';
				}
				$gradmask = '';

				// mPDF 5.7.3 TRANSFORMS
				$tr2 = '';
				if (isset($objattr['transform'])) {
					$maxsize_x = $w;
					$maxsize_y = $h;
					$cx = $x + $w / 2;
					$cy = $y + $h / 2;
					preg_match_all('/(translatex|translatey|translate|scalex|scaley|scale|rotate|skewX|skewY|skew)\((.*?)\)/is', $objattr['transform'], $m);
					if (count($m[0])) {
						for ($i = 0; $i < count($m[0]); $i++) {
							$c = strtolower($m[1][$i]);
							$v = trim($m[2][$i]);
							$vv = preg_split('/[ ,]+/', $v);
							if ($c == 'translate' && count($vv)) {
								$translate_x = $this->sizeConverter->convert($vv[0], $maxsize_x, false, false);
								if (count($vv) == 2) {
									$translate_y = $this->sizeConverter->convert($vv[1], $maxsize_y, false, false);
								} else {
									$translate_y = 0;
								}
								$tr2 .= $this->transformTranslate($translate_x, $translate_y, true) . ' ';
							} elseif ($c == 'translatex' && count($vv)) {
								$translate_x = $this->sizeConverter->convert($vv[0], $maxsize_x, false, false);
								$tr2 .= $this->transformTranslate($translate_x, 0, true) . ' ';
							} elseif ($c == 'translatey' && count($vv)) {
								$translate_y = $this->sizeConverter->convert($vv[0], $maxsize_y, false, false);
								$tr2 .= $this->transformTranslate(0, $translate_y, true) . ' ';
							} elseif ($c == 'scale' && count($vv)) {
								$scale_x = $vv[0] * 100;
								if (count($vv) == 2) {
									$scale_y = $vv[1] * 100;
								} else {
									$scale_y = $scale_x;
								}
								$tr2 .= $this->transformScale($scale_x, $scale_y, $cx, $cy, true) . ' ';
							} elseif ($c == 'scalex' && count($vv)) {
								$scale_x = $vv[0] * 100;
								$tr2 .= $this->transformScale($scale_x, 0, $cx, $cy, true) . ' ';
							} elseif ($c == 'scaley' && count($vv)) {
								$scale_y = $vv[0] * 100;
								$tr2 .= $this->transformScale(0, $scale_y, $cx, $cy, true) . ' ';
							} elseif ($c == 'skew' && count($vv)) {
								$angle_x = $this->ConvertAngle($vv[0], false);
								if (count($vv) == 2) {
									$angle_y = $this->ConvertAngle($vv[1], false);
								} else {
									$angle_y = 0;
								}
								$tr2 .= $this->transformSkew($angle_x, $angle_y, $cx, $cy, true) . ' ';
							} elseif ($c == 'skewx' && count($vv)) {
								$angle = $this->ConvertAngle($vv[0], false);
								$tr2 .= $this->transformSkew($angle, 0, $cx, $cy, true) . ' ';
							} elseif ($c == 'skewy' && count($vv)) {
								$angle = $this->ConvertAngle($vv[0], false);
								$tr2 .= $this->transformSkew(0, $angle, $cx, $cy, true) . ' ';
							} elseif ($c == 'rotate' && count($vv)) {
								$angle = $this->ConvertAngle($vv[0]);
								$tr2 .= $this->transformRotate($angle, $cx, $cy, true) . ' ';
							}
						}
					}
				}

				// LIST MARKERS (Images)	// mPDF 6  Lists
				if (isset($objattr['listmarker']) && $objattr['listmarker'] && $objattr['listmarkerposition'] == 'outside') {
					$mw = $objattr['OUTER-WIDTH'];
					// NB If change marker-offset, also need to alter in function _getListMarkerWidth
					$adjx = $this->sizeConverter->convert($this->list_marker_offset, $this->FontSize);
					if ($objattr['dir'] == 'rtl') {
						$objattr['INNER-X'] += $adjx;
					} else {
						$objattr['INNER-X'] -= $adjx;
						$objattr['INNER-X'] -= $mw;
					}
				}
				// mPDF 5.7.3 TRANSFORMS / BACKGROUND COLOR
				// Transform also affects image background
				if ($tr2) {
					$this->writer->write('q ' . $tr2 . ' ');
				}
				if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
					$bgcol = $objattr['bgcolor'];
					$this->SetFColor($bgcol);
					$this->Rect($x, $y, $w, $h, 'F');
					$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
				}
				if ($tr2) {
					$this->writer->write('Q');
				}

				/* -- BACKGROUNDS -- */
				if (isset($objattr['GRADIENT-MASK'])) {
					$g = $this->gradient->parseMozGradient($objattr['GRADIENT-MASK']);
					if ($g) {
						$dummy = $this->gradient->Gradient($objattr['INNER-X'], $objattr['INNER-Y'], $obiw, $obih, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend'], true, true);
						$gradmask = '/TGS' . count($this->gradients) . ' gs ';
					}
				}
				/* -- END BACKGROUNDS -- */
				/* -- IMAGES-WMF -- */
				if (isset($objattr['itype']) && $objattr['itype'] == 'wmf') {
					$outstring = sprintf('q ' . $tr . $tr2 . '%.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, -$sy, $objattr['INNER-X'] * Mpdf::SCALE - $sx * $objattr['wmf_x'], (($this->h - $objattr['INNER-Y']) * Mpdf::SCALE) + $sy * $objattr['wmf_y'], $objattr['ID']); // mPDF 5.7.3 TRANSFORMS
				} else { 				/* -- END IMAGES-WMF -- */
					if (isset($objattr['itype']) && $objattr['itype'] == 'svg') {
						$outstring = sprintf('q ' . $tr . $tr2 . '%.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, -$sy, $objattr['INNER-X'] * Mpdf::SCALE - $sx * $objattr['wmf_x'], (($this->h - $objattr['INNER-Y']) * Mpdf::SCALE) + $sy * $objattr['wmf_y'], $objattr['ID']); // mPDF 5.7.3 TRANSFORMS
					} else {
						$outstring = sprintf("q " . $tr . $tr2 . "%.3F 0 0 %.3F %.3F %.3F cm " . $gradmask . "/I%d Do Q", $obiw * Mpdf::SCALE, $obih * Mpdf::SCALE, $objattr['INNER-X'] * Mpdf::SCALE, ($this->h - ($objattr['INNER-Y'] + $obih )) * Mpdf::SCALE, $objattr['ID']); // mPDF 5.7.3 TRANSFORMS
					}
				}
				$this->writer->write($outstring);
				// LINK
				if (isset($objattr['link'])) {
					$this->Link($objattr['INNER-X'], $objattr['INNER-Y'], $objattr['INNER-WIDTH'], $objattr['INNER-HEIGHT'], $objattr['link']);
				}
				if (isset($objattr['opacity'])) {
					$this->SetAlpha(1);
				}

				// mPDF 5.7.3 TRANSFORMS
				// Transform also affects image borders
				if ($tr2) {
					$this->writer->write('q ' . $tr2 . ' ');
				}
				if ((isset($objattr['border_top']) && $objattr['border_top'] > 0) || (isset($objattr['border_left']) && $objattr['border_left'] > 0) || (isset($objattr['border_right']) && $objattr['border_right'] > 0) || (isset($objattr['border_bottom']) && $objattr['border_bottom'] > 0)) {
					$this->PaintImgBorder($objattr, $is_table);
				}
				if ($tr2) {
					$this->writer->write('Q');
				}

				if (isset($objattr['visibility']) && $objattr['visibility'] != 'visible' && $objattr['visibility']) {
					$this->SetVisibility('visible');
				}
				if (isset($objattr['z-index']) && $objattr['z-index'] > 0 && $this->current_layer == 0) {
					$this->EndLayer();
				}
				// mPDF 5.7.3 TRANSFORMS
				if (isset($objattr['transform'])) {
					$this->writer->write("\n" . '% ETR'); // End Transform
				}
			}

			if ($objattr['type'] === 'barcode') {

				$bgcol = $this->colorConverter->convert(255, $this->PDFAXwarnings);

				if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
					$bgcol = $objattr['bgcolor'];
				}

				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);

				if (isset($objattr['color']) && $objattr['color']) {
					$col = $objattr['color'];
				}

				$this->SetFColor($bgcol);
				$this->Rect($objattr['BORDER-X'], $objattr['BORDER-Y'], $objattr['BORDER-WIDTH'], $objattr['BORDER-HEIGHT'], 'F');
				$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));

				if (isset($objattr['BORDER-WIDTH'])) {
					$this->PaintImgBorder($objattr, $is_table);
				}

				$barcodeTypes = ['EAN13', 'ISBN', 'ISSN', 'UPCA', 'UPCE', 'EAN8'];
				if (in_array($objattr['btype'], $barcodeTypes, true)) {

					$this->WriteBarcode(
						$objattr['code'],
						$objattr['showtext'],
						$objattr['INNER-X'],
						$objattr['INNER-Y'],
						$objattr['bsize'],
						0,
						0,
						0,
						0,
						0,
						$objattr['bheight'],
						$bgcol,
						$col,
						$objattr['btype'],
						$objattr['bsupp'],
						(isset($objattr['bsupp_code']) ? $objattr['bsupp_code'] : ''),
						$k
					);

				} elseif ($objattr['btype'] === 'QR') {

					if (!class_exists('Mpdf\QrCode\QrCode') || !class_exists('Mpdf\QrCode\Output\Mpdf')) {
						throw new \Mpdf\MpdfException('Mpdf\QrCode package was not found. Install the package from Packagist with "composer require mpdf/qrcode"');
					}

					$barcodeContent = str_replace('\r\n', "\r\n", $objattr['code']);
					$barcodeContent = str_replace('\n', "\n", $barcodeContent);

					$qrcode = new QrCode\QrCode($barcodeContent, $objattr['errorlevel']);
					if ($objattr['disableborder']) {
						$qrcode->disableBorder();
					}

					$bgColor = [255, 255, 255];
					if ($objattr['bgcolor']) {
						$bgColor = array_map(
							function ($col) {
								return intval(255 * floatval($col));
							},
							explode(" ", $this->SetColor($objattr['bgcolor'], 'CodeOnly'))
						);
					}
					$color = [0, 0, 0];
					if ($objattr['color']) {
						$color = array_map(
							function ($col) {
								return intval(255 * floatval($col));
							},
							explode(" ", $this->SetColor($objattr['color'], 'CodeOnly'))
						);
					}

					$out = new QrCode\Output\Mpdf();
					$out->output(
						$qrcode,
						$this,
						$objattr['INNER-X'],
						$objattr['INNER-Y'],
						$objattr['bsize'] * 25,
						$bgColor,
						$color
					);

					unset($qrcode);

				} else {
					$this->WriteBarcode2(
						$objattr['code'],
						$objattr['INNER-X'],
						$objattr['INNER-Y'],
						$objattr['bsize'],
						$objattr['bheight'],
						$bgcol,
						$col,
						$objattr['btype'],
						$objattr['pr_ratio'],
						$k,
						$objattr['quiet_zone_left'],
						$objattr['quiet_zone_right']
					);
				}
			}

			// TEXT CIRCLE
			if ($objattr['type'] == 'textcircle') {
				$bgcol = '';
				if (isset($objattr['bgcolor']) && $objattr['bgcolor']) {
					$bgcol = $objattr['bgcolor'];
				}
				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);
				if (isset($objattr['color']) && $objattr['color']) {
					$col = $objattr['color'];
				}
				$this->SetTColor($col);
				$this->SetFColor($bgcol);
				if ($bgcol) {
					$this->Rect($objattr['BORDER-X'], $objattr['BORDER-Y'], $objattr['BORDER-WIDTH'], $objattr['BORDER-HEIGHT'], 'F');
				}
				$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
				if (isset($objattr['BORDER-WIDTH'])) {
					$this->PaintImgBorder($objattr, $is_table);
				}
				if (empty($this->directWrite)) {
					$this->directWrite = new DirectWrite($this, $this->otl, $this->sizeConverter, $this->colorConverter);
				}
				if (isset($objattr['top-text'])) {
					$this->directWrite->CircularText($objattr['INNER-X'] + $objattr['INNER-WIDTH'] / 2, $objattr['INNER-Y'] + $objattr['INNER-HEIGHT'] / 2, $objattr['r'] / $k, $objattr['top-text'], 'top', $objattr['fontfamily'], $objattr['fontsize'] / $k, $objattr['fontstyle'], $objattr['space-width'], $objattr['char-width'], (isset($objattr['divider']) ? $objattr['divider'] : ''));
				}
				if (isset($objattr['bottom-text'])) {
					$this->directWrite->CircularText($objattr['INNER-X'] + $objattr['INNER-WIDTH'] / 2, $objattr['INNER-Y'] + $objattr['INNER-HEIGHT'] / 2, $objattr['r'] / $k, $objattr['bottom-text'], 'bottom', $objattr['fontfamily'], $objattr['fontsize'] / $k, $objattr['fontstyle'], $objattr['space-width'], $objattr['char-width'], (isset($objattr['divider']) ? $objattr['divider'] : ''));
				}
			}

			$this->ResetSpacing();

			// LIST MARKERS (Text or bullets)	// mPDF 6  Lists
			if ($objattr['type'] == 'listmarker') {
				if (isset($objattr['fontfamily'])) {
					$this->SetFont($objattr['fontfamily'], $objattr['fontstyle'], $objattr['fontsizept']);
				}
				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);
				if (isset($objattr['colorarray']) && ($objattr['colorarray'])) {
					$col = $objattr['colorarray'];
				}

				if (isset($objattr['bullet']) && $objattr['bullet']) { // Used for position "outside" only
					$type = $objattr['bullet'];
					$size = $objattr['size'];

					if ($objattr['listmarkerposition'] == 'inside') {
						$adjx = $size / 2;
						if ($objattr['dir'] == 'rtl') {
							$adjx += $objattr['offset'];
						}
						$this->x += $adjx;
					} else {
						$adjx = $objattr['offset'];
						$adjx += $size / 2;
						if ($objattr['dir'] == 'rtl') {
							$this->x += $adjx;
						} else {
							$this->x -= $adjx;
						}
					}

					$yadj = $objattr['lineBox']['glyphYorigin'];
					if (isset($this->CurrentFont['desc']['XHeight']) && $this->CurrentFont['desc']['XHeight']) {
						$xh = $this->CurrentFont['desc']['XHeight'];
					} else {
						$xh = 500;
					}
					$yadj -= ($this->FontSize * $xh / 1000) * 0.625; // Vertical height of bullet (centre) from baseline= XHeight * 0.625
					$this->y += $yadj;

					$this->_printListBullet($this->x, $this->y, $size, $type, $col);
				} else {
					$this->SetTColor($col);
					$w = $this->GetStringWidth($texto);
					// NB If change marker-offset, also need to alter in function _getListMarkerWidth
					$adjx = $this->sizeConverter->convert($this->list_marker_offset, $this->FontSize);
					if ($objattr['dir'] == 'rtl') {
						$align = 'L';
						$this->x += $adjx;
					} else {
						// Use these lines to set as marker-offset, right-aligned - default
						$align = 'R';
						$this->x -= $adjx;
						$this->x -= $w;
					}
					$this->Cell($w, $this->FontSize, $texto, 0, 0, $align, 0, '', 0, 0, 0, 'T', 0, false, false, 0, $objattr['lineBox']);
					$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
				}
			}

			// DOT-TAB
			if ($objattr['type'] == 'dottab') {
				if (isset($objattr['fontfamily'])) {
					$this->SetFont($objattr['fontfamily'], '', $objattr['fontsize']);
				}
				$sp = $this->GetStringWidth(' ');
				$nb = floor(($w - 2 * $sp) / $this->GetStringWidth('.'));
				if ($nb > 0) {
					$dots = ' ' . str_repeat('.', $nb) . ' ';
				} else {
					$dots = ' ';
				}
				$col = $this->colorConverter->convert(0, $this->PDFAXwarnings);
				if (isset($objattr['colorarray']) && ($objattr['colorarray'])) {
					$col = $objattr['colorarray'];
				}
				$this->SetTColor($col);
				$save_dh = $this->divheight;
				$save_sbd = $this->spanborddet;
				$save_textvar = $this->textvar; // mPDF 5.7.1
				$this->spanborddet = '';
				$this->divheight = 0;
				$this->textvar = 0x00; // mPDF 5.7.1

				$this->Cell($w, $h, $dots, 0, 0, 'C', 0, '', 0, 0, 0, 'T', 0, false, false, 0, $objattr['lineBox']); // mPDF 6 DOTTAB
				$this->spanborddet = $save_sbd;
				$this->textvar = $save_textvar; // mPDF 5.7.1
				$this->divheight = $save_dh;
				$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			}

			/* -- FORMS -- */
			// TEXT/PASSWORD INPUT
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD')) {
				$this->form->print_ob_text($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}

			// TEXTAREA
			if ($objattr['type'] == 'textarea') {
				$this->form->print_ob_textarea($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}

			// SELECT
			if ($objattr['type'] == 'select') {
				$this->form->print_ob_select($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}


			// INPUT/BUTTON as IMAGE
			if ($objattr['type'] == 'input' && $objattr['subtype'] == 'IMAGE') {
				$this->form->print_ob_imageinput($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir, $is_table);
			}

			// BUTTON
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'SUBMIT' || $objattr['subtype'] == 'RESET' || $objattr['subtype'] == 'BUTTON')) {
				$this->form->print_ob_button($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir);
			}

			// CHECKBOX
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'CHECKBOX')) {
				$this->form->print_ob_checkbox($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir, $x, $y);
			}
			// RADIO
			if ($objattr['type'] == 'input' && ($objattr['subtype'] == 'RADIO')) {
				$this->form->print_ob_radio($objattr, $w, $h, $texto, $rtlalign, $k, $blockdir, $x, $y);
			}
			/* -- END FORMS -- */
		}

		$this->SetFont($save_currentfontfamily, $save_currentfontstyle, $save_currentfontsize);

		$this->y = $save_y;
		$this->x = $save_x;

		unset($content);
	}

	function _printListBullet($x, $y, $size, $type, $color)
	{
		// x and y are the centre of the bullet; size is the width and/or height in mm
		$fcol = $this->SetTColor($color, true);
		$lcol = strtoupper($fcol); // change 0 0 0 rg to 0 0 0 RG
		$this->writer->write(sprintf('q %s %s', $lcol, $fcol));
		$this->writer->write('0 j 0 J [] 0 d');
		if ($type == 'square') {
			$size *= 0.85; // Smaller to appear the same size as circle/disc
			$this->writer->write(sprintf('%.3F %.3F %.3F %.3F re f', ($x - $size / 2) * Mpdf::SCALE, ($this->h - $y + $size / 2) * Mpdf::SCALE, ($size) * Mpdf::SCALE, (-$size) * Mpdf::SCALE));
		} elseif ($type == 'disc') {
			$this->Circle($x, $y, $size / 2, 'F'); // Fill
		} elseif ($type == 'circle') {
			$lw = $size / 12; // Line width
			$this->writer->write(sprintf('%.3F w ', $lw * Mpdf::SCALE));
			$this->Circle($x, $y, $size / 2 - $lw / 2, 'S'); // Stroke
		}
		$this->writer->write('Q');
	}

	// mPDF 6
	// Get previous character and move pointers
	function _moveToPrevChar(&$contentctr, &$charctr, $content)
	{
		$lastchar = false;
		$charctr--;
		while ($charctr < 0) { // go back to previous $content[]
			$contentctr--;
			if ($contentctr < 0) {
				return false;
			}
			if ($this->usingCoreFont) {
				$charctr = strlen($content[$contentctr]) - 1;
			} else {
				$charctr = mb_strlen($content[$contentctr], $this->mb_enc) - 1;
			}
		}
		if ($this->usingCoreFont) {
			$lastchar = $content[$contentctr][$charctr];
		} else {
			$lastchar = mb_substr($content[$contentctr], $charctr, 1, $this->mb_enc);
		}
		return $lastchar;
	}

	// Get previous character
	function _getPrevChar($contentctr, $charctr, $content)
	{
		$lastchar = false;
		$charctr--;
		while ($charctr < 0) { // go back to previous $content[]
			$contentctr--;
			if ($contentctr < 0) {
				return false;
			}
			if ($this->usingCoreFont) {
				$charctr = strlen($content[$contentctr]) - 1;
			} else {
				$charctr = mb_strlen($content[$contentctr], $this->mb_enc) - 1;
			}
		}
		if ($this->usingCoreFont) {
			$lastchar = $content[$contentctr][$charctr];
		} else {
			$lastchar = mb_substr($content[$contentctr], $charctr, 1, $this->mb_enc);
		}
		return $lastchar;
	}

	function WriteFlowingBlock($s, $sOTLdata)
	{
	// mPDF 5.7.1
		$currentx = $this->x;
		$is_table = $this->flowingBlockAttr['is_table'];
		$table_draft = $this->flowingBlockAttr['table_draft'];
		// width of all the content so far in points
		$contentWidth = & $this->flowingBlockAttr['contentWidth'];
		// cell width in points
		$maxWidth = & $this->flowingBlockAttr['width'];
		$lineCount = & $this->flowingBlockAttr['lineCount'];
		// line height in user units
		$stackHeight = & $this->flowingBlockAttr['height'];
		$align = & $this->flowingBlockAttr['align'];
		$content = & $this->flowingBlockAttr['content'];
		$contentB = & $this->flowingBlockAttr['contentB'];
		$font = & $this->flowingBlockAttr['font'];
		$valign = & $this->flowingBlockAttr['valign'];
		$blockstate = $this->flowingBlockAttr['blockstate'];
		$cOTLdata = & $this->flowingBlockAttr['cOTLdata']; // mPDF 5.7.1

		$newblock = $this->flowingBlockAttr['newblock'];
		$blockdir = $this->flowingBlockAttr['blockdir'];

		// *********** BLOCK BACKGROUND COLOR ***************** //
		if ($this->blk[$this->blklvl]['bgcolor'] && !$is_table) {
			$fill = 0;
		} else {
			$this->SetFColor($this->colorConverter->convert(255, $this->PDFAXwarnings));
			$fill = 0;
		}
		$font[] = $this->saveFont();
		$content[] = '';
		$contentB[] = '';
		$cOTLdata[] = $sOTLdata; // mPDF 5.7.1
		$currContent = & $content[count($content) - 1];

		$CJKoverflow = false;
		$Oikomi = false; // mPDF 6
		$hanger = '';

		// COLS
		$oldcolumn = $this->CurrCol;
		if ($this->ColActive && !$is_table) {
			$this->breakpoints[$this->CurrCol][] = $this->y;
		} // *COLUMNS*

		/* -- TABLES -- */
		if ($is_table) {
			$ipaddingL = 0;
			$ipaddingR = 0;
			$paddingL = 0;
			$paddingR = 0;
			$cpaddingadjustL = 0;
			$cpaddingadjustR = 0;
			// Added mPDF 3.0
			$fpaddingR = 0;
			$fpaddingL = 0;
		} else {
			/* -- END TABLES -- */
			$ipaddingL = $this->blk[$this->blklvl]['padding_left'];
			$ipaddingR = $this->blk[$this->blklvl]['padding_right'];
			$paddingL = ($ipaddingL * Mpdf::SCALE);
			$paddingR = ($ipaddingR * Mpdf::SCALE);
			$this->cMarginL = $this->blk[$this->blklvl]['border_left']['w'];
			$cpaddingadjustL = -$this->cMarginL;
			$this->cMarginR = $this->blk[$this->blklvl]['border_right']['w'];
			$cpaddingadjustR = -$this->cMarginR;
			// Added mPDF 3.0 Float DIV
			$fpaddingR = 0;
			$fpaddingL = 0;
			/* -- CSS-FLOAT -- */
			if (count($this->floatDivs)) {
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
				if ($r_exists) {
					$fpaddingR = $r_width;
				}
				if ($l_exists) {
					$fpaddingL = $l_width;
				}
			}
			/* -- END CSS-FLOAT -- */

			$usey = $this->y + 0.002;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
				$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
			}
			/* -- CSS-IMAGE-FLOAT -- */
			// If float exists at this level
			if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
				$fpaddingR += $this->floatmargins['R']['w'];
			}
			if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
				$fpaddingL += $this->floatmargins['L']['w'];
			}
			/* -- END CSS-IMAGE-FLOAT -- */
		} // *TABLES*
		// OBJECTS - IMAGES & FORM Elements (NB has already skipped line/page if required - in printbuffer)
		if (substr($s, 0, 3) == Mpdf::OBJECT_IDENTIFIER) { // identifier has been identified!
			$objattr = $this->_getObjAttr($s);
			$h_corr = 0;
			if ($is_table) { // *TABLES*
				$maximumW = ($maxWidth / Mpdf::SCALE) - ($this->cellPaddingL + $this->cMarginL + $this->cellPaddingR + $this->cMarginR);  // *TABLES*
			} // *TABLES*
			else { // *TABLES*
				if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0) && (!$is_table)) {
					$h_corr = $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
				}
				$maximumW = ($maxWidth / Mpdf::SCALE) - ($this->blk[$this->blklvl]['padding_left'] + $this->blk[$this->blklvl]['border_left']['w'] + $this->blk[$this->blklvl]['padding_right'] + $this->blk[$this->blklvl]['border_right']['w'] + $fpaddingL + $fpaddingR );
			} // *TABLES*
			$objattr = $this->inlineObject($objattr['type'], $this->lMargin + $fpaddingL + ($contentWidth / Mpdf::SCALE), ($this->y + $h_corr), $objattr, $this->lMargin, ($contentWidth / Mpdf::SCALE), $maximumW, $stackHeight, true, $is_table);

			// SET LINEHEIGHT for this line ================ RESET AT END
			$stackHeight = max($stackHeight, $objattr['OUTER-HEIGHT']);
			$this->objectbuffer[count($content) - 1] = $objattr;
			// if (isset($objattr['vertical-align'])) { $valign = $objattr['vertical-align']; }
			// else { $valign = ''; }
			// LIST MARKERS	// mPDF 6  Lists
			if ($objattr['type'] == 'image' && isset($objattr['listmarker']) && $objattr['listmarker'] && $objattr['listmarkerposition'] == 'outside') {
				// do nothing
			} else {
				$contentWidth += ($objattr['OUTER-WIDTH'] * Mpdf::SCALE);
			}
			return;
		}

		$lbw = $rbw = 0; // Border widths
		if (!empty($this->spanborddet)) {
			if (isset($this->spanborddet['L'])) {
				$lbw = $this->spanborddet['L']['w'];
			}
			if (isset($this->spanborddet['R'])) {
				$rbw = $this->spanborddet['R']['w'];
			}
		}

		if ($this->usingCoreFont) {
			$clen = strlen($s);
		} else {
			$clen = mb_strlen($s, $this->mb_enc);
		}

		// for every character in the string
		for ($i = 0; $i < $clen; $i++) {
			// extract the current character
			// get the width of the character in points
			if ($this->usingCoreFont) {
				$c = $s[$i];
				// Soft Hyphens chr(173)
				$cw = ($this->GetCharWidthCore($c) * Mpdf::SCALE);
				if (($this->textvar & TextVars::FC_KERNING) && $i > 0) { // mPDF 5.7.1
					if (isset($this->CurrentFont['kerninfo'][$s[($i - 1)]][$c])) {
						$cw += ($this->CurrentFont['kerninfo'][$s[($i - 1)]][$c] * $this->FontSizePt / 1000 );
					}
				}
			} else {
				$c = mb_substr($s, $i, 1, $this->mb_enc);
				$cw = ($this->GetCharWidthNonCore($c, false) * Mpdf::SCALE);
				// mPDF 5.7.1
				// Use OTL GPOS
				if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF)) {
					// ...WriteFlowingBlock...
					// Only  add XAdvanceL (not sure at present whether RTL or LTR writing direction)
					// At this point, XAdvanceL and XAdvanceR will balance
					if (isset($sOTLdata['GPOSinfo'][$i]['XAdvanceL'])) {
						$cw += $sOTLdata['GPOSinfo'][$i]['XAdvanceL'] * (1000 / $this->CurrentFont['unitsPerEm']) * ($this->FontSize / 1000) * Mpdf::SCALE;
					}
				}
				if (($this->textvar & TextVars::FC_KERNING) && $i > 0) { // mPDF 5.7.1
					$lastc = mb_substr($s, ($i - 1), 1, $this->mb_enc);
					$ulastc = $this->UTF8StringToArray($lastc, false);
					$uc = $this->UTF8StringToArray($c, false);
					if (isset($this->CurrentFont['kerninfo'][$ulastc[0]][$uc[0]])) {
						$cw += ($this->CurrentFont['kerninfo'][$ulastc[0]][$uc[0]] * $this->FontSizePt / 1000 );
					}
				}
			}

			if ($i == 0) {
				$cw += $lbw * Mpdf::SCALE;
				$contentB[(count($contentB) - 1)] .= 'L';
			}
			if ($i == ($clen - 1)) {
				$cw += $rbw * Mpdf::SCALE;
				$contentB[(count($contentB) - 1)] .= 'R';
			}
			if ($c == ' ') {
				$currContent .= $c;
				$contentWidth += $cw;
				continue;
			}

			// Paragraph INDENT
			$WidthCorrection = 0;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && isset($this->blk[$this->blklvl]['text_indent']) && ($lineCount == 0) && (!$is_table) && ($align != 'C')) {
				$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
				$WidthCorrection = ($ti * Mpdf::SCALE);
			}
			// OUTDENT
			foreach ($this->objectbuffer as $k => $objattr) {   // mPDF 6 DOTTAB
				if ($objattr['type'] == 'dottab') {
					$WidthCorrection -= ($objattr['outdent'] * Mpdf::SCALE);
					break;
				}
			}


			// Added mPDF 3.0 Float DIV
			$fpaddingR = 0;
			$fpaddingL = 0;
			/* -- CSS-FLOAT -- */
			if (count($this->floatDivs)) {
				list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($this->blklvl);
				if ($r_exists) {
					$fpaddingR = $r_width;
				}
				if ($l_exists) {
					$fpaddingL = $l_width;
				}
			}
			/* -- END CSS-FLOAT -- */

			$usey = $this->y + 0.002;
			if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 0)) {
				$usey += $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'];
			}

			/* -- CSS-IMAGE-FLOAT -- */
			// If float exists at this level
			if (isset($this->floatmargins['R']) && $usey <= $this->floatmargins['R']['y1'] && $usey >= $this->floatmargins['R']['y0'] && !$this->floatmargins['R']['skipline']) {
				$fpaddingR += $this->floatmargins['R']['w'];
			}
			if (isset($this->floatmargins['L']) && $usey <= $this->floatmargins['L']['y1'] && $usey >= $this->floatmargins['L']['y0'] && !$this->floatmargins['L']['skipline']) {
				$fpaddingL += $this->floatmargins['L']['w'];
			}
			/* -- END CSS-IMAGE-FLOAT -- */


			// try adding another char
			if (( $contentWidth + $cw > $maxWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) ) + 0.001)) {// 0.001 is to correct for deviations converting mm=>pts
				// it won't fit, output what we already have
				$lineCount++;

				// contains any content that didn't make it into this print
				$savedContent = '';
				$savedContentB = '';
				$savedOTLdata = []; // mPDF 5.7.1
				$savedFont = [];
				$savedObj = [];
				$savedPreOTLdata = []; // mPDF 5.7.1
				$savedPreContent = [];
				$savedPreContentB = [];
				$savedPreFont = [];

				// mPDF 6
				// New line-breaking algorithm
				/////////////////////
				// LINE BREAKING
				/////////////////////
				$breakfound = false;
				$contentctr = count($content) - 1;
				if ($this->usingCoreFont) {
					$charctr = strlen($currContent);
				} else {
					$charctr = mb_strlen($currContent, $this->mb_enc);
				}
				$checkchar = $c;
				$prevchar = $this->_getPrevChar($contentctr, $charctr, $content);

				/* -- CJK-FONTS -- */
				// 1) CJK Overflowing a) punctuation or b) Oikomi
				// Next character ($c) is suitable to add as overhanging or squeezed punctuation, or Oikomi
				if ($CJKoverflow || $Oikomi) { // If flag already set
					$CJKoverflow = false;
					$Oikomi = false;
					$breakfound = true;
				}
				if (!$this->usingCoreFont && !$breakfound && $this->checkCJK) {

					// Get next/following character (in this chunk)
					$followingchar = '';
					if ($i < ($clen - 1)) {
						if ($this->usingCoreFont) {
							$followingchar = $s[$i + 1];
						} else {
							$followingchar = mb_substr($s, $i + 1, 1, $this->mb_enc);
						}
					}

					// 1a) Overflow punctuation
					if (preg_match("/[" . $this->pregCJKchars . "]/u", $prevchar) && preg_match("/[" . $this->CJKoverflow . "]/u", $checkchar) && $this->allowCJKorphans) {
						// add character onto this line
						$currContent .= $c;
						$contentWidth += $cw;
						$CJKoverflow = true; // Set flag
						continue;
					} elseif (preg_match("/[" . $this->pregCJKchars . "]/u", $checkchar) && $this->allowCJKorphans &&
							(preg_match("/[" . $this->CJKleading . "]/u", $followingchar) || preg_match("/[" . $this->CJKfollowing . "]/u", $checkchar)) &&
							!preg_match("/[" . $this->CJKleading . "]/u", $checkchar) && !preg_match("/[" . $this->CJKfollowing . "]/u", $followingchar) &&
							!(preg_match("/[0-9\x{ff10}-\x{ff19}]/u", $followingchar) && preg_match("/[0-9\x{ff10}-\x{ff19}]/u", $checkchar))) {
						// 1b) Try squeezing another character(s) onto this line = Oikomi, if character cannot end line
						// or next character cannot start line (and not splitting CJK numerals)
						// NB otherwise it move lastchar(s) to next line to keep $c company = Oidashi, which is done below in standard way
						// add character onto this line
						$currContent .= $c;
						$contentWidth += $cw;
						$Oikomi = true; // Set flag
						continue;
					}
				}
				/* -- END CJK-FONTS -- */
				/* -- HYPHENATION -- */

				// AUTOMATIC HYPHENATION
				// 2) Automatic hyphen in current word (does not cross tags)
				if (isset($this->textparam['hyphens']) && $this->textparam['hyphens'] == 1) {
					$currWord = '';
					// Look back and ahead to get current word
					for ($ac = $charctr - 1; $ac >= 0; $ac--) {
						if ($this->usingCoreFont) {
							$addc = substr($currContent, $ac, 1);
						} else {
							$addc = mb_substr($currContent, $ac, 1, $this->mb_enc);
						}
						if ($addc == ' ') {
							break;
						}
						$currWord = $addc . $currWord;
					}
					$start = $ac + 1;
					for ($ac = $i; $ac < ($clen - 1); $ac++) {
						if ($this->usingCoreFont) {
							$addc = substr($s, $ac, 1);
						} else {
							$addc = mb_substr($s, $ac, 1, $this->mb_enc);
						}
						if ($addc == ' ') {
							break;
						}
						$currWord .= $addc;
					}
					$ptr = $this->hyphenator->hyphenateWord($currWord, $charctr - $start);
					if ($ptr > -1) {
						$breakfound = [$contentctr, $start + $ptr, $contentctr, $start + $ptr, 'hyphen'];
					}
				}
				/* -- END HYPHENATION -- */

				// Search backwards to find first line-break opportunity
				while ($breakfound == false && $prevchar !== false) {
					$cutcontentctr = $contentctr;
					$cutcharctr = $charctr;
					$prevchar = $this->_moveToPrevChar($contentctr, $charctr, $content);
					/////////////////////
					// 3) Break at SPACE
					/////////////////////
					if ($prevchar == ' ') {
						$breakfound = [$contentctr, $charctr, $cutcontentctr, $cutcharctr, 'discard'];
					} /////////////////////
					// 4) Break at U+200B in current word (Khmer, Lao & Thai Invisible word boundary, and Tibetan)
					/////////////////////
					elseif ($prevchar == "\xe2\x80\x8b") { // U+200B Zero-width Word Break
						$breakfound = [$contentctr, $charctr, $cutcontentctr, $cutcharctr, 'discard'];
					} /////////////////////
					// 5) Break at Hard HYPHEN '-' or U+2010
					/////////////////////
					elseif (isset($this->textparam['hyphens']) && $this->textparam['hyphens'] != 2 && ($prevchar == '-' || $prevchar == "\xe2\x80\x90")) {
						// Don't break a URL
						// Look back to get first part of current word
						$checkw = '';
						for ($ac = $charctr - 1; $ac >= 0; $ac--) {
							if ($this->usingCoreFont) {
								$addc = substr($currContent, $ac, 1);
							} else {
								$addc = mb_substr($currContent, $ac, 1, $this->mb_enc);
							}
							if ($addc == ' ') {
								break;
							}
							$checkw = $addc . $checkw;
						}
						// Don't break if HyphenMinus AND (a URL or before a numeral or before a >)
						if ((!preg_match('/(http:|ftp:|https:|www\.)/', $checkw) && $checkchar != '>' && !preg_match('/[0-9]/', $checkchar)) || $prevchar == "\xe2\x80\x90") {
							$breakfound = [$cutcontentctr, $cutcharctr, $cutcontentctr, $cutcharctr, 'cut'];
						}
					} /////////////////////
					// 6) Break at Soft HYPHEN (replace with hard hyphen)
					/////////////////////
					elseif (isset($this->textparam['hyphens']) && $this->textparam['hyphens'] != 2 && !$this->usingCoreFont && $prevchar == "\xc2\xad") {
						$breakfound = [$cutcontentctr, $cutcharctr, $cutcontentctr, $cutcharctr, 'cut'];
						$content[$contentctr] = mb_substr($content[$contentctr], 0, $charctr, $this->mb_enc) . '-' . mb_substr($content[$contentctr], $charctr + 1, mb_strlen($content[$contentctr]), $this->mb_enc);
						if (!empty($cOTLdata[$contentctr])) {
							$cOTLdata[$contentctr]['char_data'][$charctr] = ['bidi_class' => 9, 'uni' => 45];
							$cOTLdata[$contentctr]['group'][$charctr] = 'C';
						}
					} elseif (isset($this->textparam['hyphens']) && $this->textparam['hyphens'] != 2 && $this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats' && $prevchar == chr(173)) {
						$breakfound = [$cutcontentctr, $cutcharctr, $cutcontentctr, $cutcharctr, 'cut'];
						$content[$contentctr] = substr($content[$contentctr], 0, $charctr) . '-' . substr($content[$contentctr], $charctr + 1);
					} /* -- CJK-FONTS -- */
					/////////////////////
					// 7) Break at CJK characters (unless forbidden characters to end or start line)
					// CJK Avoiding line break in the middle of numerals
					/////////////////////
					elseif (!$this->usingCoreFont && $this->checkCJK && preg_match("/[" . $this->pregCJKchars . "]/u", $checkchar) &&
						!preg_match("/[" . $this->CJKfollowing . "]/u", $checkchar) && !preg_match("/[" . $this->CJKleading . "]/u", $prevchar) &&
						!(preg_match("/[0-9\x{ff10}-\x{ff19}]/u", $prevchar) && preg_match("/[0-9\x{ff10}-\x{ff19}]/u", $checkchar))) {
						$breakfound = [$cutcontentctr, $cutcharctr, $cutcontentctr, $cutcharctr, 'cut'];
					}
					/* -- END CJK-FONTS -- */
					/////////////////////
					// 8) Break at OBJECT (Break before all objects here - selected objects are moved forward to next line below e.g. dottab)
					/////////////////////
					if (isset($this->objectbuffer[$contentctr])) {
						$breakfound = [$cutcontentctr, $cutcharctr, $cutcontentctr, $cutcharctr, 'cut'];
					}


					$checkchar = $prevchar;
				}

				// If a line-break opportunity found:
				if (is_array($breakfound)) {
					$contentctr = $breakfound[0];
					$charctr = $breakfound[1];
					$cutcontentctr = $breakfound[2];
					$cutcharctr = $breakfound[3];
					$type = $breakfound[4];
					// Cache chunks which are already processed, but now need to be passed on to the new line
					for ($ix = count($content) - 1; $ix > $cutcontentctr; $ix--) {
						// save and crop off any subsequent chunks
						/* -- OTL -- */
						if (!empty($sOTLdata)) {
							$tmpOTL = array_pop($cOTLdata);
							$savedPreOTLdata[] = $tmpOTL;
						}
						/* -- END OTL -- */
						$savedPreContent[] = array_pop($content);
						$savedPreContentB[] = array_pop($contentB);
						$savedPreFont[] = array_pop($font);
					}

					// Next cache the part which will start the next line
					if ($this->usingCoreFont) {
						$savedPreContent[] = substr($content[$cutcontentctr], $cutcharctr);
					} else {
						$savedPreContent[] = mb_substr($content[$cutcontentctr], $cutcharctr, mb_strlen($content[$cutcontentctr]), $this->mb_enc);
					}
					$savedPreContentB[] = preg_replace('/L/', '', $contentB[$cutcontentctr]);
					$savedPreFont[] = $font[$cutcontentctr];
					/* -- OTL -- */
					if (!empty($sOTLdata)) {
						$savedPreOTLdata[] = $this->otl->splitOTLdata($cOTLdata[$cutcontentctr], $cutcharctr, $cutcharctr);
					}
					/* -- END OTL -- */


					// Finally adjust the Current content which ends this line
					if ($cutcharctr == 0 && $type == 'discard') {
						array_pop($content);
						array_pop($contentB);
						array_pop($font);
						array_pop($cOTLdata);
					}

					$currContent = & $content[count($content) - 1];
					if ($this->usingCoreFont) {
						$currContent = substr($currContent, 0, $charctr);
					} else {
						$currContent = mb_substr($currContent, 0, $charctr, $this->mb_enc);
					}

					if (!empty($sOTLdata)) {
						$savedPreOTLdata[] = $this->otl->splitOTLdata($cOTLdata[(count($cOTLdata) - 1)], mb_strlen($currContent, $this->mb_enc));
					}

					if (strpos($contentB[(count($contentB) - 1)], 'R') !== false) {   // ???
						$contentB[count($content) - 1] = preg_replace('/R/', '', $contentB[count($content) - 1]); // ???
					}

					if ($type === 'hyphen') {
						$hyphen = in_array(mb_substr($currContent, -1), ['-', '–', '—'], true);
						if (!$hyphen) {
							$currContent .= '-';
						} else {
							$savedPreContent[count($savedPreContent) - 1] = '-' . $savedPreContent[count($savedPreContent) - 1];
						}
						if (!empty($cOTLdata[(count($cOTLdata) - 1)])) {
							$cOTLdata[(count($cOTLdata) - 1)]['char_data'][] = ['bidi_class' => 9, 'uni' => 45];
							$cOTLdata[(count($cOTLdata) - 1)]['group'] .= 'C';
						}
					}

					$savedContent = '';
					$savedContentB = '';
					$savedFont = [];
					$savedOTLdata = [];
				}
				// If no line-break opportunity found - split at current position
				// or - Next character ($c) is suitable to add as overhanging or squeezed punctuation, or Oikomi, as set above by:
				// 1) CJK Overflowing a) punctuation or b) Oikomi
				// in which case $breakfound==1 and NOT array

				if (!is_array($breakfound)) {
					$savedFont = $this->saveFont();
					if (!empty($sOTLdata)) {
						$savedOTLdata = $this->otl->splitOTLdata($cOTLdata[(count($cOTLdata) - 1)], mb_strlen($currContent, $this->mb_enc));
					}
				}

				if ($content[count($content) - 1] == '' && !isset($this->objectbuffer[count($content) - 1])) {
					array_pop($content);
					array_pop($contentB);
					array_pop($font);
					array_pop($cOTLdata);
					$currContent = & $content[count($content) - 1];
				}

				// Right Trim current content - including CJK space, and for OTLdata
				// incl. CJK - strip CJK space at end of line &#x3000; = \xe3\x80\x80 = CJK space
				$currContent = $currContent ? rtrim($currContent) : '';
				if ($this->checkCJK) {
					$currContent = preg_replace("/\xe3\x80\x80$/", '', $currContent);
				} // *CJK-FONTS*
				/* -- OTL -- */
				if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
					$this->otl->trimOTLdata($cOTLdata[count($cOTLdata) - 1], false, true); // NB also does U+3000
				}
				/* -- END OTL -- */


				// Selected OBJECTS are moved forward to next line, unless they come before a space or U+200B (type='discard')
				if (isset($this->objectbuffer[(count($content) - 1)]) && (!isset($type) || $type != 'discard')) {
					$objtype = $this->objectbuffer[(count($content) - 1)]['type'];
					if ($objtype == 'dottab' || $objtype == 'bookmark' || $objtype == 'indexentry' || $objtype == 'toc' || $objtype == 'annot') {
						$savedObj = array_pop($this->objectbuffer);
					}
				}


				// Decimal alignment (cancel if wraps to > 1 line)
				if ($is_table && substr($align, 0, 1) == 'D') {
					$align = substr($align, 2, 1);
				}

				$lineBox = [];

				$this->_setInlineBlockHeights($lineBox, $stackHeight, $content, $font, $is_table);

				// update $contentWidth since it has changed with cropping
				$contentWidth = 0;

				$inclCursive = false;
				foreach ($content as $k => $chunk) {
					if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
						// LIST MARKERS
						if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker']) {
							if ($this->objectbuffer[$k]['listmarkerposition'] != 'outside') {
								$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * Mpdf::SCALE;
							}
						} else {
							$contentWidth += $this->objectbuffer[$k]['OUTER-WIDTH'] * Mpdf::SCALE;
						}
					} elseif (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
						$this->restoreFont($font[$k], false);
						if ($this->checkCJK && $k == count($content) - 1 && $CJKoverflow && $align == 'J' && $this->allowCJKoverflow && $this->CJKforceend) {
							// force-end overhang
							$hanger = mb_substr($chunk, mb_strlen($chunk, $this->mb_enc) - 1, 1, $this->mb_enc);
							// Probably ought to do something with char_data and GPOS in cOTLdata...
							$content[$k] = $chunk = mb_substr($chunk, 0, mb_strlen($chunk, $this->mb_enc) - 1, $this->mb_enc);
						}

						// Soft Hyphens chr(173) + Replace NBSP with SPACE + Set inclcursive if includes CURSIVE TEXT
						if (!$this->usingCoreFont) {
							/* -- OTL -- */
							if ((isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) || !empty($sOTLdata)) {
								$this->otl->removeChar($chunk, $cOTLdata[$k], "\xc2\xad");
								$this->otl->replaceSpace($chunk, $cOTLdata[$k]); // NBSP -> space
								if (preg_match("/([" . $this->pregCURSchars . "])/u", $chunk)) {
									$inclCursive = true;
								}
								$content[$k] = $chunk;
							} /* -- END OTL -- */ else {  // *OTL*
								$content[$k] = $chunk = str_replace("\xc2\xad", '', $chunk);
								$content[$k] = $chunk = str_replace(chr(194) . chr(160), chr(32), $chunk);
							} // *OTL*
						} elseif ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
							$content[$k] = $chunk = str_replace(chr(173), '', $chunk);
							$content[$k] = $chunk = str_replace(chr(160), chr(32), $chunk);
						}

						$contentWidth += $this->GetStringWidth($chunk, true, (isset($cOTLdata[$k]) ? $cOTLdata[$k] : false), $this->textvar) * Mpdf::SCALE;  // mPDF 5.7.1
						if (!empty($this->spanborddet)) {
							if (isset($this->spanborddet['L']['w']) && strpos($contentB[$k], 'L') !== false) {
								$contentWidth += $this->spanborddet['L']['w'] * Mpdf::SCALE;
							}
							if (isset($this->spanborddet['R']['w']) && strpos($contentB[$k], 'R') !== false) {
								$contentWidth += $this->spanborddet['R']['w'] * Mpdf::SCALE;
							}
						}
					}
				}

				$lastfontreqstyle = (isset($font[count($font) - 1]['ReqFontStyle']) ? $font[count($font) - 1]['ReqFontStyle'] : '');
				$lastfontstyle = (isset($font[count($font) - 1]['style']) ? $font[count($font) - 1]['style'] : '');
				if ($blockdir == 'ltr' && strpos($lastfontreqstyle, "I") !== false && strpos($lastfontstyle, "I") === false) { // Artificial italic
					$lastitalic = $this->FontSize * 0.15 * Mpdf::SCALE;
				} else {
					$lastitalic = 0;
				}




				// NOW FORMAT THE LINE TO OUTPUT
				if (!$table_draft) {
					// DIRECTIONALITY RTL
					$chunkorder = range(0, count($content) - 1); // mPDF 5.7
					/* -- OTL -- */
					// mPDF 6
					if ($blockdir == 'rtl' || $this->biDirectional) {
						$this->otl->bidiReorder($chunkorder, $content, $cOTLdata, $blockdir);
						// From this point on, $content and $cOTLdata may contain more elements (and re-ordered) compared to
						// $this->objectbuffer and $font ($chunkorder contains the mapping)
					}

					/* -- END OTL -- */
					// Remove any XAdvance from OTL data at end of line
					foreach ($chunkorder as $aord => $k) {
						if (count($cOTLdata)) {
							$this->restoreFont($font[$k], false);
							// ...WriteFlowingBlock...
							if ($aord == count($chunkorder) - 1 && isset($cOTLdata[$aord]['group'])) { // Last chunk on line
								$nGPOS = strlen($cOTLdata[$aord]['group']) - 1; // Last character
								if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL']) || isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'])) {
									if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'])) {
										$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] * 1000 / $this->CurrentFont['unitsPerEm'];
									} else {
										$w = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] * 1000 / $this->CurrentFont['unitsPerEm'];
									}
									$w *= ($this->FontSize / 1000);
									$contentWidth -= $w * Mpdf::SCALE;
									$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = 0;
									$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = 0;
								}

								// If last character has an XPlacement set, adjust width calculation, and add to XAdvance to account for it
								if (isset($cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'])) {
									$w = -$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'] * 1000 / $this->CurrentFont['unitsPerEm'];
									$w *= ($this->FontSize / 1000);
									$contentWidth -= $w * Mpdf::SCALE;
									$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceL'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
									$cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XAdvanceR'] = $cOTLdata[$aord]['GPOSinfo'][$nGPOS]['XPlacement'];
								}
							}
						}
					}

					// JUSTIFICATION J
					$jcharspacing = 0;
					$jws = 0;
					$nb_carac = 0;
					$nb_spaces = 0;
					$jkashida = 0;
					// if it's justified, we need to find the char/word spacing (or if hanger $this->CJKforceend)
					if (($align == 'J' && !$CJKoverflow) || (($contentWidth + $lastitalic > $maxWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) ) + 0.001) && (!$CJKoverflow || ($CJKoverflow && !$this->allowCJKoverflow))) || $CJKoverflow && $align == 'J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {   // 0.001 is to correct for deviations converting mm=>pts
						// JUSTIFY J (Use character spacing)
						// WORD SPACING
						// mPDF 5.7
						foreach ($chunkorder as $aord => $k) {
							$chunk = isset($content[$aord]) ? $content[$aord] : '';
							if (!isset($this->objectbuffer[$k]) || (isset($this->objectbuffer[$k]) && !$this->objectbuffer[$k])) {
								$nb_carac += mb_strlen($chunk, $this->mb_enc);
								$nb_spaces += mb_substr_count($chunk, ' ', $this->mb_enc);
								// Use GPOS OTL
								if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF)) {
									if (isset($cOTLdata[$aord]['group']) && $cOTLdata[$aord]['group']) {
										$nb_carac -= substr_count($cOTLdata[$aord]['group'], 'M');
									}
								}
							} else {
								$nb_carac ++;
							} // mPDF 6 allow spacing for inline object
						}
						// GetJSpacing adds kashida spacing to GPOSinfo if appropriate for Font
						list($jcharspacing, $jws, $jkashida) = $this->GetJspacing($nb_carac, $nb_spaces, ($maxWidth - $lastitalic - $contentWidth - $WidthCorrection - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) )), $inclCursive, $cOTLdata);
					}

					// WORD SPACING
					$empty = $maxWidth - $lastitalic - $WidthCorrection - $contentWidth - (($this->cMarginL + $this->cMarginR) * Mpdf::SCALE) - ($paddingL + $paddingR + (($fpaddingL + $fpaddingR) * Mpdf::SCALE) );

					$empty -= ($jcharspacing * ($nb_carac - 1)); // mPDF 6 nb_carac MINUS 1
					$empty -= ($jws * $nb_spaces);
					$empty -= ($jkashida);
					$empty /= Mpdf::SCALE;

					$b = ''; // do not use borders
					// Get PAGEBREAK TO TEST for height including the top border/padding
					$check_h = max($this->divheight, $stackHeight);
					if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($this->blklvl > 0) && ($lineCount == 1) && (!$is_table)) {
						$check_h += ($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['margin_top'] + $this->blk[$this->blklvl]['border_top']['w']);
					}

					if ($this->ColActive && $check_h > ($this->PageBreakTrigger - $this->y0)) {
						$this->SetCol($this->NbCol - 1);
					}

					// PAGEBREAK
					// 'If' below used in order to fix "first-line of other page with justify on" bug
					if (!$is_table && ($this->y + $check_h) > $this->PageBreakTrigger and ! $this->InFooter and $this->AcceptPageBreak()) {
						$bak_x = $this->x; // Current X position
						// WORD SPACING
						$ws = $this->ws; // Word Spacing
						$charspacing = $this->charspacing; // Character Spacing
						$this->ResetSpacing();

						$this->AddPage($this->CurOrientation);

						$this->x = $bak_x;
						// Added to correct for OddEven Margins
						$currentx += $this->MarginCorrection;
						$this->x += $this->MarginCorrection;

						// WORD SPACING
						$this->SetSpacing($charspacing, $ws);
					}

					if ($this->kwt && !$is_table) { // mPDF 5.7+
						$this->printkwtbuffer();
						$this->kwt = false;
					}


					/* -- COLUMNS -- */
					// COLS
					// COLUMN CHANGE
					if ($this->CurrCol != $oldcolumn) {
						$currentx += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
						$this->x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
						$oldcolumn = $this->CurrCol;
					}

					if ($this->ColActive && !$is_table) {
						$this->breakpoints[$this->CurrCol][] = $this->y;
					} // *COLUMNS*
					/* -- END COLUMNS -- */

					// TOP MARGIN
					if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($this->blk[$this->blklvl]['margin_top']) && ($lineCount == 1) && (!$is_table)) {
						$this->DivLn($this->blk[$this->blklvl]['margin_top'], $this->blklvl - 1, true, $this->blk[$this->blklvl]['margin_collapse']);
						if ($this->ColActive) {
							$this->breakpoints[$this->CurrCol][] = $this->y;
						} // *COLUMNS*
					}


					// Update y0 for top of block (used to paint border)
					if (($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 1) && (!$is_table)) {
						$this->blk[$this->blklvl]['y0'] = $this->y;
						$this->blk[$this->blklvl]['startpage'] = $this->page;
						if ($this->blk[$this->blklvl]['float']) {
							$this->blk[$this->blklvl]['float_start_y'] = $this->y;
						}
					}

					// TOP PADDING and BORDER spacing/fill
					if (($newblock) && ($blockstate == 1 || $blockstate == 3) && (($this->blk[$this->blklvl]['padding_top']) || ($this->blk[$this->blklvl]['border_top'])) && ($lineCount == 1) && (!$is_table)) {
						// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
						$this->DivLn($this->blk[$this->blklvl]['padding_top'] + $this->blk[$this->blklvl]['border_top']['w'], -3, true, false, 1);
						if ($this->ColActive) {
							$this->breakpoints[$this->CurrCol][] = $this->y;
						} // *COLUMNS*
					}

					$arraysize = count($chunkorder);

					$margins = ($this->cMarginL + $this->cMarginR) + ($ipaddingL + $ipaddingR + $fpaddingR + $fpaddingR );

					// PAINT BACKGROUND FOR THIS LINE
					if (!$is_table) {
						$this->DivLn($stackHeight, $this->blklvl, false);
					} // false -> don't advance y

					$this->x = $currentx + $this->cMarginL + $ipaddingL + $fpaddingL;
					if ($align == 'R') {
						$this->x += $empty;
					} elseif ($align == 'C') {
						$this->x += ($empty / 2);
					}

					// Paragraph INDENT
					if (isset($this->blk[$this->blklvl]['text_indent']) && ($newblock) && ($blockstate == 1 || $blockstate == 3) && ($lineCount == 1) && (!$is_table) && ($blockdir != 'rtl') && ($align != 'C')) {
						$ti = $this->sizeConverter->convert($this->blk[$this->blklvl]['text_indent'], $this->blk[$this->blklvl]['inner_width'], $this->blk[$this->blklvl]['InlineProperties']['size'], false);  // mPDF 5.7.4
						$this->x += $ti;
					}

					// BIDI magic_reverse moved upwards from here
					foreach ($chunkorder as $aord => $k) { // mPDF 5.7

						$chunk = isset($content[$aord]) ? $content[$aord] : '';

						if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]) {
							$xadj = $this->x - $this->objectbuffer[$k]['OUTER-X'];
							$this->objectbuffer[$k]['OUTER-X'] += $xadj;
							$this->objectbuffer[$k]['BORDER-X'] += $xadj;
							$this->objectbuffer[$k]['INNER-X'] += $xadj;

							if ($this->objectbuffer[$k]['type'] == 'listmarker') {
								$this->objectbuffer[$k]['lineBox'] = $lineBox[-1]; // Block element details for glyph-origin
							}
							$yadj = $this->y - $this->objectbuffer[$k]['OUTER-Y'];
							if ($this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
								$this->objectbuffer[$k]['lineBox'] = $lineBox[$k]; // element details for glyph-origin
							}
							if ($this->objectbuffer[$k]['type'] != 'dottab') { // mPDF 6 DOTTAB
								$yadj += $lineBox[$k]['top'];
							}
							$this->objectbuffer[$k]['OUTER-Y'] += $yadj;
							$this->objectbuffer[$k]['BORDER-Y'] += $yadj;
							$this->objectbuffer[$k]['INNER-Y'] += $yadj;
						}

						$this->restoreFont($font[$k]);  // mPDF 5.7

						$this->SetSpacing(($this->fixedlSpacing * Mpdf::SCALE) + $jcharspacing, ($this->fixedlSpacing + $this->minwSpacing) * Mpdf::SCALE + $jws);
						// Now unset these values so they don't influence GetStringwidth below or in fn. Cell
						$this->fixedlSpacing = false;
						$this->minwSpacing = 0;

						$save_vis = $this->visibility;
						if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->textparam['visibility'] != $this->visibility) {
							$this->SetVisibility($this->textparam['visibility']);
						}
						// *********** SPAN BACKGROUND COLOR ***************** //
						if ($this->spanbgcolor) {
							$cor = $this->spanbgcolorarray;
							$this->SetFColor($cor);
							$save_fill = $fill;
							$spanfill = 1;
							$fill = 1;
						}
						if (!empty($this->spanborddet)) {
							if (strpos($contentB[$k], 'L') !== false) {
								$this->x += (isset($this->spanborddet['L']['w']) ? $this->spanborddet['L']['w'] : 0);
							}
							if (strpos($contentB[$k], 'L') === false) {
								$this->spanborddet['L']['s'] = $this->spanborddet['L']['w'] = 0;
							}
							if (strpos($contentB[$k], 'R') === false) {
								$this->spanborddet['R']['s'] = $this->spanborddet['R']['w'] = 0;
							}
						}

						// WORD SPACING
						// StringWidth this time includes any kashida spacing
						$stringWidth = $this->GetStringWidth($chunk, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, true);

						$nch = mb_strlen($chunk, $this->mb_enc);
						// Use GPOS OTL
						if (isset($this->CurrentFont['useOTL']) && ($this->CurrentFont['useOTL'] & 0xFF)) {
							if (isset($cOTLdata[$aord]['group']) && $cOTLdata[$aord]['group']) {
								$nch -= substr_count($cOTLdata[$aord]['group'], 'M');
							}
						}
						$stringWidth += ( $this->charspacing * $nch / Mpdf::SCALE );

						$stringWidth += ( $this->ws * mb_substr_count($chunk, ' ', $this->mb_enc) / Mpdf::SCALE );

						if (isset($this->objectbuffer[$k])) {
							// LIST MARKERS	// mPDF 6  Lists
							if ($this->objectbuffer[$k]['type'] == 'image' && isset($this->objectbuffer[$k]['listmarker']) && $this->objectbuffer[$k]['listmarker'] && $this->objectbuffer[$k]['listmarkerposition'] == 'outside') {
								$stringWidth = 0;
							} else {
								$stringWidth = $this->objectbuffer[$k]['OUTER-WIDTH'];
							}
						}

						if ($stringWidth == 0) {
							$stringWidth = 0.000001;
						}

						if ($aord == $arraysize - 1) {
							$stringWidth -= ( $this->charspacing / Mpdf::SCALE );
							if ($this->checkCJK && $CJKoverflow && $align == 'J' && $this->allowCJKoverflow && $hanger && $this->CJKforceend) {
								// force-end overhang
								$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false));
								$this->Cell($this->GetStringWidth($hanger), $stackHeight, $hanger, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false));
							} else {
								$this->Cell($stringWidth, $stackHeight, $chunk, '', 1, '', $fill, $this->HREF, $currentx, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // mono-style line or last part (skips line)
							}
						} else {
							$this->Cell($stringWidth, $stackHeight, $chunk, '', 0, '', $fill, $this->HREF, 0, 0, 0, 'M', $fill, true, (isset($cOTLdata[$aord]) ? $cOTLdata[$aord] : false), $this->textvar, (isset($lineBox[$k]) ? $lineBox[$k] : false)); // first or middle part
						}

						if (!empty($this->spanborddet)) {
							if (strpos($contentB[$k], 'R') !== false && $aord != $arraysize - 1) {
								$this->x += $this->spanborddet['R']['w'];
							}
						}
						// *********** SPAN BACKGROUND COLOR OFF - RESET BLOCK BGCOLOR ***************** //
						if (isset($spanfill) && $spanfill) {
							$fill = $save_fill;
							$spanfill = 0;
							if ($fill) {
								$this->SetFColor($bcor);
							}
						}
						if (isset($this->textparam['visibility']) && $this->textparam['visibility'] && $this->visibility != $save_vis) {
							$this->SetVisibility($save_vis);
						}
					}
				} elseif ($table_draft) {
					$this->y += $stackHeight;
				}

				if (!$is_table) {
					$this->maxPosR = max($this->maxPosR, ($this->w - $this->rMargin - $this->blk[$this->blklvl]['outer_right_margin']));
					$this->maxPosL = min($this->maxPosL, ($this->lMargin + $this->blk[$this->blklvl]['outer_left_margin']));
				}

				// move on to the next line, reset variables, tack on saved content and current char

				if (!$table_draft) {
					$this->printobjectbuffer($is_table, $blockdir);
				}
				$this->objectbuffer = [];


				/* -- CSS-IMAGE-FLOAT -- */
				// Update values if set to skipline
				if ($this->floatmargins) {
					$this->_advanceFloatMargins();
				}
				/* -- END CSS-IMAGE-FLOAT -- */

				// Reset lineheight
				$stackHeight = $this->divheight;
				$valign = 'M';

				$font = [];
				$content = [];
				$contentB = [];
				$cOTLdata = []; // mPDF 5.7.1
				$contentWidth = 0;
				if (!empty($savedObj)) {
					$this->objectbuffer[] = $savedObj;
					$font[] = $savedFont;
					$content[] = '';
					$contentB[] = '';
					$cOTLdata[] = []; // mPDF 5.7.1
					$contentWidth += $savedObj['OUTER-WIDTH'] * Mpdf::SCALE;
				}
				if (count($savedPreContent) > 0) {
					for ($ix = count($savedPreContent) - 1; $ix >= 0; $ix--) {
						$font[] = $savedPreFont[$ix];
						$content[] = $savedPreContent[$ix];
						$contentB[] = $savedPreContentB[$ix];
						if (!empty($sOTLdata)) {
							$cOTLdata[] = $savedPreOTLdata[$ix];
						}
						$this->restoreFont($savedPreFont[$ix]);
						$lbw = $rbw = 0; // Border widths
						if (!empty($this->spanborddet)) {
							$lbw = (isset($this->spanborddet['L']['w']) ? $this->spanborddet['L']['w'] : 0);
							$rbw = (isset($this->spanborddet['R']['w']) ? $this->spanborddet['R']['w'] : 0);
						}
						if ($ix > 0) {
							$contentWidth += $this->GetStringWidth($savedPreContent[$ix], true, (isset($savedPreOTLdata[$ix]) ? $savedPreOTLdata[$ix] : false), $this->textvar) * Mpdf::SCALE; // mPDF 5.7.1
							if (strpos($savedPreContentB[$ix], 'L') !== false) {
								$contentWidth += $lbw;
							}
							if (strpos($savedPreContentB[$ix], 'R') !== false) {
								$contentWidth += $rbw;
							}
						}
					}
					$savedPreContent = [];
					$savedPreContentB = [];
					$savedPreOTLdata = []; // mPDF 5.7.1
					$savedPreFont = [];
					$content[(count($content) - 1)] .= $c;
				} else {
					$font[] = $savedFont;
					$content[] = $savedContent . $c;
					$contentB[] = $savedContentB;
					$cOTLdata[] = $savedOTLdata; // mPDF 5.7.1
				}

				$currContent = & $content[(count($content) - 1)];
				$this->restoreFont($font[(count($font) - 1)]); // mPDF 6.0

				/* -- CJK-FONTS -- */
				// CJK - strip CJK space at start of line
				// &#x3000; = \xe3\x80\x80 = CJK space
				if ($this->checkCJK && $currContent == "\xe3\x80\x80") {
					$currContent = '';
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
						$this->otl->trimOTLdata($cOTLdata[count($cOTLdata) - 1], true, false); // left trim U+3000
					}
				}
				/* -- END CJK-FONTS -- */

				$lbw = $rbw = 0; // Border widths
				if (!empty($this->spanborddet)) {
					$lbw = (isset($this->spanborddet['L']['w']) ? $this->spanborddet['L']['w'] : 0);
					$rbw = (isset($this->spanborddet['R']['w']) ? $this->spanborddet['R']['w'] : 0);
				}

				$contentWidth += $this->GetStringWidth($currContent, false, (isset($cOTLdata[(count($cOTLdata) - 1)]) ? $cOTLdata[(count($cOTLdata) - 1)] : false), $this->textvar) * Mpdf::SCALE; // mPDF 5.7.1
				if (strpos($savedContentB, 'L') !== false) {
					$contentWidth += $lbw;
				}
				$CJKoverflow = false;
				$hanger = '';
			} // another character will fit, so add it on
			else {
				$contentWidth += $cw;
				$currContent .= $c;
			}
		}

		unset($content);
		unset($contentB);
	}

	// ----------------------END OF FLOWING BLOCK------------------------------------//


	/* -- CSS-IMAGE-FLOAT -- */
	// Update values if set to skipline
	function _advanceFloatMargins()
	{
		// Update floatmargins - L
		if (isset($this->floatmargins['L']) && $this->floatmargins['L']['skipline'] && $this->floatmargins['L']['y0'] != $this->y) {
			$yadj = $this->y - $this->floatmargins['L']['y0'];
			$this->floatmargins['L']['y0'] = $this->y;
			$this->floatmargins['L']['y1'] += $yadj;

			// Update objattr in floatbuffer
			if ($this->floatbuffer[$this->floatmargins['L']['id']]['border_left']['w']) {
				$this->floatbuffer[$this->floatmargins['L']['id']]['BORDER-Y'] += $yadj;
			}
			$this->floatbuffer[$this->floatmargins['L']['id']]['INNER-Y'] += $yadj;
			$this->floatbuffer[$this->floatmargins['L']['id']]['OUTER-Y'] += $yadj;

			// Unset values
			$this->floatbuffer[$this->floatmargins['L']['id']]['skipline'] = false;
			$this->floatmargins['L']['skipline'] = false;
			$this->floatmargins['L']['id'] = '';
		}
		// Update floatmargins - R
		if (isset($this->floatmargins['R']) && $this->floatmargins['R']['skipline'] && $this->floatmargins['R']['y0'] != $this->y) {
			$yadj = $this->y - $this->floatmargins['R']['y0'];
			$this->floatmargins['R']['y0'] = $this->y;
			$this->floatmargins['R']['y1'] += $yadj;

			// Update objattr in floatbuffer
			if ($this->floatbuffer[$this->floatmargins['R']['id']]['border_left']['w']) {
				$this->floatbuffer[$this->floatmargins['R']['id']]['BORDER-Y'] += $yadj;
			}
			$this->floatbuffer[$this->floatmargins['R']['id']]['INNER-Y'] += $yadj;
			$this->floatbuffer[$this->floatmargins['R']['id']]['OUTER-Y'] += $yadj;

			// Unset values
			$this->floatbuffer[$this->floatmargins['R']['id']]['skipline'] = false;
			$this->floatmargins['R']['skipline'] = false;
			$this->floatmargins['R']['id'] = '';
		}
	}

	/* -- END CSS-IMAGE-FLOAT -- */



	/* -- END HTML-CSS -- */

	function _SetTextRendering($mode)
	{
		if (!(($mode == 0) || ($mode == 1) || ($mode == 2))) {
			throw new \Mpdf\MpdfException("Text rendering mode should be 0, 1 or 2 (value : $mode)");
		}
		$tr = ($mode . ' Tr');
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']))) {
			$this->writer->write($tr);
		}
		$this->pageoutput[$this->page]['TextRendering'] = $tr;
	}

	function SetTextOutline($params = [])
	{
		if (isset($params['outline-s']) && $params['outline-s']) {
			$this->SetLineWidth($params['outline-WIDTH']);
			$this->SetDColor($params['outline-COLOR']);
			$tr = ('2 Tr');
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']))) {
				$this->writer->write($tr);
			}
			$this->pageoutput[$this->page]['TextRendering'] = $tr;
		} else { // Now resets all values
			$this->SetLineWidth(0.2);
			$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			$this->_SetTextRendering(0);
			$tr = ('0 Tr');
			if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['TextRendering']) && $this->pageoutput[$this->page]['TextRendering'] != $tr) || !isset($this->pageoutput[$this->page]['TextRendering']))) {
				$this->writer->write($tr);
			}
			$this->pageoutput[$this->page]['TextRendering'] = $tr;
		}
	}

	function Image($file, $x, $y, $w = 0, $h = 0, $type = '', $link = '', $paint = true, $constrain = true, $watermark = false, $shownoimg = true, $allowvector = true)
	{
		$orig_srcpath = $file;
		$this->GetFullPath($file);

		$info = $this->imageProcessor->getImage($file, true, $allowvector, $orig_srcpath);
		if (!$info && $paint) {
			$info = $this->imageProcessor->getImage($this->noImageFile);
			if ($info) {
				$file = $this->noImageFile;
				$w = ($info['w'] * (25.4 / $this->img_dpi));  // 14 x 16px
				$h = ($info['h'] * (25.4 / $this->img_dpi));  // 14 x 16px
			}
		}
		if (!$info) {
			return false;
		}
		// Automatic width and height calculation if needed
		if ($w == 0 and $h == 0) {
			/* -- IMAGES-WMF -- */
			if ($info['type'] === 'wmf') {
				// WMF units are twips (1/20pt)
				// divide by 20 to get points
				// divide by k to get user units
				$w = abs($info['w']) / (20 * Mpdf::SCALE);
				$h = abs($info['h']) / (20 * Mpdf::SCALE);
			} else { 			/* -- END IMAGES-WMF -- */
				if ($info['type'] === 'svg') {
					// returned SVG units are pts
					// divide by k to get user units (mm)
					$w = abs($info['w']) / Mpdf::SCALE;
					$h = abs($info['h']) / Mpdf::SCALE;
				} else {
					// Put image at default image dpi
					$w = ($info['w'] / Mpdf::SCALE) * (72 / $this->img_dpi);
					$h = ($info['h'] / Mpdf::SCALE) * (72 / $this->img_dpi);
				}
			}
		}
		if ($w == 0) {
			$w = abs($h * $info['w'] / $info['h']);
		}
		if ($h == 0) {
			$h = abs($w * $info['h'] / $info['w']);
		}

		/* -- WATERMARK -- */
		if ($watermark) {
			$maxw = $this->w;
			$maxh = $this->h;
			// Size = D PF or array
			if (is_array($this->watermark_size)) {
				$w = $this->watermark_size[0];
				$h = $this->watermark_size[1];
			} elseif (!is_string($this->watermark_size)) {
				$maxw -= $this->watermark_size * 2;
				$maxh -= $this->watermark_size * 2;
				$w = $maxw;
				$h = abs($w * $info['h'] / $info['w']);
				if ($h > $maxh) {
					$h = $maxh;
					$w = abs($h * $info['w'] / $info['h']);
				}
			} elseif ($this->watermark_size == 'F') {
				if ($this->ColActive) {
					$maxw = $this->w - ($this->DeflMargin + $this->DefrMargin);
				} else {
					$maxw = $this->pgwidth;
				}
				$maxh = $this->h - ($this->tMargin + $this->bMargin);
				$w = $maxw;
				$h = abs($w * $info['h'] / $info['w']);
				if ($h > $maxh) {
					$h = $maxh;
					$w = abs($h * $info['w'] / $info['h']);
				}
			} elseif ($this->watermark_size == 'P') { // Default P
				$w = $maxw;
				$h = abs($w * $info['h'] / $info['w']);
				if ($h > $maxh) {
					$h = $maxh;
					$w = abs($h * $info['w'] / $info['h']);
				}
			}
			// Automatically resize to maximum dimensions of page if too large
			if ($w > $maxw) {
				$w = $maxw;
				$h = abs($w * $info['h'] / $info['w']);
			}
			if ($h > $maxh) {
				$h = $maxh;
				$w = abs($h * $info['w'] / $info['h']);
			}
			// Position
			if (is_array($this->watermark_pos)) {
				$x = $this->watermark_pos[0];
				$y = $this->watermark_pos[1];
			} elseif ($this->watermark_pos == 'F') { // centred on printable area
				if ($this->ColActive) { // *COLUMNS*
					if (($this->mirrorMargins) && (($this->page) % 2 == 0)) {
						$xadj = $this->DeflMargin - $this->DefrMargin;
					} // *COLUMNS*
					else {
						$xadj = 0;
					} // *COLUMNS*
					$x = ($this->DeflMargin - $xadj + ($this->w - ($this->DeflMargin + $this->DefrMargin)) / 2) - ($w / 2); // *COLUMNS*
				} // *COLUMNS*
				else {  // *COLUMNS*
					$x = ($this->lMargin + ($this->pgwidth) / 2) - ($w / 2);
				} // *COLUMNS*
				$y = ($this->tMargin + ($this->h - ($this->tMargin + $this->bMargin)) / 2) - ($h / 2);
			} else { // default P - centred on whole page
				$x = ($this->w / 2) - ($w / 2);
				$y = ($this->h / 2) - ($h / 2);
			}
			/* -- IMAGES-WMF -- */
			if ($info['type'] == 'wmf') {
				$sx = $w * Mpdf::SCALE / $info['w'];
				$sy = -$h * Mpdf::SCALE / $info['h'];
				$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x * Mpdf::SCALE - $sx * $info['x'], (($this->h - $y) * Mpdf::SCALE) - $sy * $info['y'], $info['i']);
			} else { 			/* -- END IMAGES-WMF -- */
				if ($info['type'] == 'svg') {
					$sx = $w * Mpdf::SCALE / $info['w'];
					$sy = -$h * Mpdf::SCALE / $info['h'];
					$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x * Mpdf::SCALE - $sx * $info['x'], (($this->h - $y) * Mpdf::SCALE) - $sy * $info['y'], $info['i']);
				} else {
					$outstring = sprintf("q %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $w * Mpdf::SCALE, $h * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($y + $h)) * Mpdf::SCALE, $info['i']);
				}
			}

			if ($this->watermarkImgBehind) {
				$outstring = $this->watermarkImgAlpha . "\n" . $outstring . "\n" . $this->SetAlpha(1, 'Normal', true) . "\n";
				$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/', "\n" . $outstring . "\n" . '\\1', $this->pages[$this->page]);
			} else {
				$this->writer->write($outstring);
			}

			return 0;
		} // end of IF watermark
		/* -- END WATERMARK -- */

		if ($constrain) {
			// Automatically resize to maximum dimensions of page if too large
			if (isset($this->blk[$this->blklvl]['inner_width']) && $this->blk[$this->blklvl]['inner_width']) {
				$maxw = $this->blk[$this->blklvl]['inner_width'];
			} else {
				$maxw = $this->pgwidth;
			}
			if ($w > $maxw) {
				$w = $maxw;
				$h = abs($w * $info['h'] / $info['w']);
			}
			if ($h > $this->h - ($this->tMargin + $this->bMargin + 1)) {  // see below - +10 to avoid drawing too close to border of page
				$h = $this->h - ($this->tMargin + $this->bMargin + 1);
				if ($this->fullImageHeight) {
					$h = $this->fullImageHeight;
				}
				$w = abs($h * $info['w'] / $info['h']);
			}


			// Avoid drawing out of the paper(exceeding width limits).
			// if ( ($x + $w) > $this->fw ) {
			if (($x + $w) > $this->w) {
				$x = $this->lMargin;
				$y += 5;
			}

			$changedpage = false;
			$oldcolumn = $this->CurrCol;
			// Avoid drawing out of the page.
			if ($y + $h > $this->PageBreakTrigger and ! $this->InFooter and $this->AcceptPageBreak()) {
				$this->AddPage($this->CurOrientation);
				// Added to correct for OddEven Margins
				$x = $x + $this->MarginCorrection;
				$y = $this->tMargin; // mPDF 5.7.3
				$changedpage = true;
			}
			/* -- COLUMNS -- */
			// COLS
			// COLUMN CHANGE
			if ($this->CurrCol != $oldcolumn) {
				$y = $this->y0;
				$x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
				$this->x += $this->ChangeColumn * ($this->ColWidth + $this->ColGap);
			}
			/* -- END COLUMNS -- */
		} // end of IF constrain

		/* -- IMAGES-WMF -- */
		if ($info['type'] == 'wmf') {
			$sx = $w * Mpdf::SCALE / $info['w'];
			$sy = -$h * Mpdf::SCALE / $info['h'];
			$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x * Mpdf::SCALE - $sx * $info['x'], (($this->h - $y) * Mpdf::SCALE) - $sy * $info['y'], $info['i']);
		} else { 		/* -- END IMAGES-WMF -- */
			if ($info['type'] == 'svg') {
				$sx = $w * Mpdf::SCALE / $info['w'];
				$sy = -$h * Mpdf::SCALE / $info['h'];
				$outstring = sprintf('q %.3F 0 0 %.3F %.3F %.3F cm /FO%d Do Q', $sx, $sy, $x * Mpdf::SCALE - $sx * $info['x'], (($this->h - $y) * Mpdf::SCALE) - $sy * $info['y'], $info['i']);
			} else {
				$outstring = sprintf("q %.3F 0 0 %.3F %.3F %.3F cm /I%d Do Q", $w * Mpdf::SCALE, $h * Mpdf::SCALE, $x * Mpdf::SCALE, ($this->h - ($y + $h)) * Mpdf::SCALE, $info['i']);
			}
		}

		if ($paint) {
			$this->writer->write($outstring);
			if ($link) {
				$this->Link($x, $y, $w, $h, $link);
			}

			// Avoid writing text on top of the image. // THIS WAS OUTSIDE THE if ($paint) bit!!!!!!!!!!!!!!!!
			$this->y = $y + $h;
		}

		// Return width-height array
		$sizesarray['WIDTH'] = $w;
		$sizesarray['HEIGHT'] = $h;
		$sizesarray['X'] = $x; // Position before painting image
		$sizesarray['Y'] = $y; // Position before painting image
		$sizesarray['OUTPUT'] = $outstring;

		$sizesarray['IMAGE_ID'] = $info['i'];
		$sizesarray['itype'] = $info['type'];
		$sizesarray['set-dpi'] = (isset($info['set-dpi']) ? $info['set-dpi'] : 0);
		return $sizesarray;
	}

	// =============================================================
	// =============================================================
	// =============================================================
	// =============================================================
	// =============================================================
	/* -- HTML-CSS -- */

	function _getObjAttr($t)
	{
		$c = explode(Mpdf::OBJECT_IDENTIFIER, $t, 2);
		$c = explode(',', $c[1], 2);

		foreach ($c as $v) {
			$v = explode('=', $v, 2);
			$sp[$v[0]] = trim($v[1], Mpdf::OBJECT_IDENTIFIER);
		}

		return (unserialize($sp['objattr']));
	}

	function inlineObject($type, $x, $y, $objattr, $Lmargin, $widthUsed, $maxWidth, $lineHeight, $paint = false, $is_table = false)
	{
		if ($is_table) {
			$k = $this->shrin_k;
		} else {
			$k = 1;
		}

		// NB $x is only used when paint=true
		// Lmargin not used
		$w = 0;
		if (isset($objattr['width'])) {
			$w = $objattr['width'] / $k;
		}
		$h = 0;
		if (isset($objattr['height'])) {
			$h = abs($objattr['height'] / $k);
		}
		$widthLeft = $maxWidth - $widthUsed;
		$maxHeight = $this->h - ($this->tMargin + $this->bMargin + 10);
		if ($this->fullImageHeight) {
			$maxHeight = $this->fullImageHeight;
		}
		// For Images
		if (isset($objattr['border_left'])) {
			$extraWidth = ($objattr['border_left']['w'] + $objattr['border_right']['w'] + $objattr['margin_left'] + $objattr['margin_right']) / $k;
			$extraHeight = ($objattr['border_top']['w'] + $objattr['border_bottom']['w'] + $objattr['margin_top'] + $objattr['margin_bottom']) / $k;

			if ($type == 'image' || $type == 'barcode' || $type == 'textcircle') {
				$extraWidth += ($objattr['padding_left'] + $objattr['padding_right']) / $k;
				$extraHeight += ($objattr['padding_top'] + $objattr['padding_bottom']) / $k;
			}
		}

		if (!isset($objattr['vertical-align'])) {
			if ($objattr['type'] == 'select') {
				$objattr['vertical-align'] = 'M';
			} else {
				$objattr['vertical-align'] = 'BS';
			}
		} // mPDF 6

		if ($type == 'image' || (isset($objattr['subtype']) && $objattr['subtype'] == 'IMAGE')) {
			if (isset($objattr['itype']) && ($objattr['itype'] == 'wmf' || $objattr['itype'] == 'svg')) {
				$file = $objattr['file'];
				$info = $this->formobjects[$file];
			} elseif (isset($objattr['file'])) {
				$file = $objattr['file'];
				$info = $this->images[$file];
			}
		}
		if ($type == 'annot' || $type == 'bookmark' || $type == 'indexentry' || $type == 'toc') {
			$w = 0.00001;
			$h = 0.00001;
		}

		// TEST whether need to skipline
		if (!$paint) {
			if ($type == 'hr') { // always force new line
				if (($y + $h + $lineHeight > $this->PageBreakTrigger) && !$this->InFooter && !$is_table) {
					return [-2, $w, $h];
				} // New page + new line
				else {
					return [1, $w, $h];
				} // new line
			} else {
				// LIST MARKERS	// mPDF 6  Lists
				$displayheight = $h;
				$displaywidth = $w;
				if ($objattr['type'] == 'image' && isset($objattr['listmarker']) && $objattr['listmarker']) {
					$displayheight = 0;
					if ($objattr['listmarkerposition'] == 'outside') {
						$displaywidth = 0;
					}
				}

				if ($widthUsed > 0 && $displaywidth > $widthLeft && (!$is_table || $type != 'image')) {  // New line needed
					// mPDF 6  Lists
					if (($y + $displayheight + $lineHeight > $this->PageBreakTrigger) && !$this->InFooter) {
						return [-2, $w, $h];
					} // New page + new line
					return [1, $w, $h]; // new line
				} elseif ($widthUsed > 0 && $displaywidth > $widthLeft && $is_table) {  // New line needed in TABLE
					return [1, $w, $h]; // new line
				} // Will fit on line but NEW PAGE REQUIRED
				elseif (($y + $displayheight > $this->PageBreakTrigger) && !$this->InFooter && !$is_table) {
					return [-1, $w, $h];
				} // mPDF 6  Lists
				else {
					return [0, $w, $h];
				}
			}
		}

		if ($type == 'annot' || $type == 'bookmark' || $type == 'indexentry' || $type == 'toc') {
			$w = 0.00001;
			$h = 0.00001;
			$objattr['BORDER-WIDTH'] = 0;
			$objattr['BORDER-HEIGHT'] = 0;
			$objattr['BORDER-X'] = $x;
			$objattr['BORDER-Y'] = $y;
			$objattr['INNER-WIDTH'] = 0;
			$objattr['INNER-HEIGHT'] = 0;
			$objattr['INNER-X'] = $x;
			$objattr['INNER-Y'] = $y;
		}

		if ($type == 'image') {
			// Automatically resize to width remaining
			if ($w > ($widthLeft + 0.0001) && !$is_table) { // mPDF 5.7.4  0.0001 to allow for rounding errors when w==maxWidth
				$w = $widthLeft;
				$h = abs($w * $info['h'] / $info['w']);
			}
			$img_w = $w - $extraWidth;
			$img_h = $h - $extraHeight;

			$objattr['BORDER-WIDTH'] = $img_w + $objattr['padding_left'] / $k + $objattr['padding_right'] / $k + (($objattr['border_left']['w'] / $k + $objattr['border_right']['w'] / $k) / 2);
			$objattr['BORDER-HEIGHT'] = $img_h + $objattr['padding_top'] / $k + $objattr['padding_bottom'] / $k + (($objattr['border_top']['w'] / $k + $objattr['border_bottom']['w'] / $k) / 2);
			$objattr['BORDER-X'] = $x + $objattr['margin_left'] / $k + (($objattr['border_left']['w'] / $k) / 2);
			$objattr['BORDER-Y'] = $y + $objattr['margin_top'] / $k + (($objattr['border_top']['w'] / $k) / 2);
			$objattr['INNER-WIDTH'] = $img_w;
			$objattr['INNER-HEIGHT'] = $img_h;
			$objattr['INNER-X'] = $x + $objattr['padding_left'] / $k + $objattr['margin_left'] / $k + ($objattr['border_left']['w'] / $k);
			$objattr['INNER-Y'] = $y + $objattr['padding_top'] / $k + $objattr['margin_top'] / $k + ($objattr['border_top']['w'] / $k);
			$objattr['ID'] = $info['i'];
		}

		if ($type == 'input' && $objattr['subtype'] == 'IMAGE') {
			$img_w = $w - $extraWidth;
			$img_h = $h - $extraHeight;
			$objattr['BORDER-WIDTH'] = $img_w + (($objattr['border_left']['w'] / $k + $objattr['border_right']['w'] / $k) / 2);
			$objattr['BORDER-HEIGHT'] = $img_h + (($objattr['border_top']['w'] / $k + $objattr['border_bottom']['w'] / $k) / 2);
			$objattr['BORDER-X'] = $x + $objattr['margin_left'] / $k + (($objattr['border_left']['w'] / $k) / 2);
			$objattr['BORDER-Y'] = $y + $objattr['margin_top'] / $k + (($objattr['border_top']['w'] / $k) / 2);
			$objattr['INNER-WIDTH'] = $img_w;
			$objattr['INNER-HEIGHT'] = $img_h;
			$objattr['INNER-X'] = $x + $objattr['margin_left'] / $k + ($objattr['border_left']['w'] / $k);
			$objattr['INNER-Y'] = $y + $objattr['margin_top'] / $k + ($objattr['border_top']['w'] / $k);
			$objattr['ID'] = $info['i'];
		}

		if ($type == 'barcode' || $type == 'textcircle') {
			$b_w = $w - $extraWidth;
			$b_h = $h - $extraHeight;
			$objattr['BORDER-WIDTH'] = $b_w + $objattr['padding_left'] / $k + $objattr['padding_right'] / $k + (($objattr['border_left']['w'] / $k + $objattr['border_right']['w'] / $k) / 2);
			$objattr['BORDER-HEIGHT'] = $b_h + $objattr['padding_top'] / $k + $objattr['padding_bottom'] / $k + (($objattr['border_top']['w'] / $k + $objattr['border_bottom']['w'] / $k) / 2);
			$objattr['BORDER-X'] = $x + $objattr['margin_left'] / $k + (($objattr['border_left']['w'] / $k) / 2);
			$objattr['BORDER-Y'] = $y + $objattr['margin_top'] / $k + (($objattr['border_top']['w'] / $k) / 2);
			$objattr['INNER-X'] = $x + $objattr['padding_left'] / $k + $objattr['margin_left'] / $k + ($objattr['border_left']['w'] / $k);
			$objattr['INNER-Y'] = $y + $objattr['padding_top'] / $k + $objattr['margin_top'] / $k + ($objattr['border_top']['w'] / $k);
			$objattr['INNER-WIDTH'] = $b_w;
			$objattr['INNER-HEIGHT'] = $b_h;
		}


		if ($type == 'textarea') {
			// Automatically resize to width remaining
			if ($w > $widthLeft && !$is_table) {
				$w = $widthLeft;
			}
			// This used to resize height to maximum remaining on page ? why. Causes problems when in table and causing a new column
			// if (($y + $h > $this->PageBreakTrigger) && !$this->InFooter) {
			// 	$h=$this->h - $y - $this->bMargin;
			// }
		}

		if ($type == 'hr') {
			if ($is_table) {
				$objattr['INNER-WIDTH'] = $maxWidth * $objattr['W-PERCENT'] / 100;
				$objattr['width'] = $objattr['INNER-WIDTH'];
				$w = $maxWidth;
			} else {
				if ($w > $maxWidth) {
					$w = $maxWidth;
				}
				$objattr['INNER-WIDTH'] = $w;
				$w = $maxWidth;
			}
		}



		if (($type == 'select') || ($type == 'input' && ($objattr['subtype'] == 'TEXT' || $objattr['subtype'] == 'PASSWORD'))) {
			// Automatically resize to width remaining
			if ($w > $widthLeft && !$is_table) {
				$w = $widthLeft;
			}
		}

		if ($type == 'textarea' || $type == 'select' || $type == 'input') {
			if (isset($objattr['fontsize'])) {
				$objattr['fontsize'] /= $k;
			}
			if (isset($objattr['linewidth'])) {
				$objattr['linewidth'] /= $k;
			}
		}

		if (!isset($objattr['BORDER-Y'])) {
			$objattr['BORDER-Y'] = 0;
		}
		if (!isset($objattr['BORDER-X'])) {
			$objattr['BORDER-X'] = 0;
		}
		if (!isset($objattr['INNER-Y'])) {
			$objattr['INNER-Y'] = 0;
		}
		if (!isset($objattr['INNER-X'])) {
			$objattr['INNER-X'] = 0;
		}

		// Return width-height array
		$objattr['OUTER-WIDTH'] = $w;
		$objattr['OUTER-HEIGHT'] = $h;
		$objattr['OUTER-X'] = $x;
		$objattr['OUTER-Y'] = $y;
		return $objattr;
	}

	/* -- END HTML-CSS -- */

	// =============================================================
	// =============================================================
	// =============================================================
	// =============================================================
	// =============================================================

	function SetLineJoin($mode = 0)
	{
		$s = sprintf('%d j', $mode);
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['LineJoin']) && $this->pageoutput[$this->page]['LineJoin'] != $s) || !isset($this->pageoutput[$this->page]['LineJoin']))) {
			$this->writer->write($s);
		}
		$this->pageoutput[$this->page]['LineJoin'] = $s;
	}

	function SetLineCap($mode = 2)
	{
		$s = sprintf('%d J', $mode);
		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['LineCap']) && $this->pageoutput[$this->page]['LineCap'] != $s) || !isset($this->pageoutput[$this->page]['LineCap']))) {
			$this->writer->write($s);
		}
		$this->pageoutput[$this->page]['LineCap'] = $s;
	}

	function SetDash($black = false, $white = false)
	{
		if ($black and $white) {
			$s = sprintf('[%.3F %.3F] 0 d', $black * Mpdf::SCALE, $white * Mpdf::SCALE);
		} else {
			$s = '[] 0 d';
		}

		if ($this->page > 0 && ((isset($this->pageoutput[$this->page]['Dash']) && $this->pageoutput[$this->page]['Dash'] != $s) || !isset($this->pageoutput[$this->page]['Dash']))) {
			$this->writer->write($s);
		}

		$this->pageoutput[$this->page]['Dash'] = $s;
	}

	function SetDisplayPreferences($preferences)
	{
		// String containing any or none of /HideMenubar/HideToolbar/HideWindowUI/DisplayDocTitle/CenterWindow/FitWindow

		$this->DisplayPreferences .= $preferences;
	}

	function Ln($h = '', $collapsible = 0)
	{
		// Added collapsible to allow collapsible top-margin on new page
		// Line feed; default value is last cell height

		$margin = isset($this->blk[$this->blklvl]['outer_left_margin']) ? $this->blk[$this->blklvl]['outer_left_margin'] : 0;

		$this->x = $this->lMargin + $margin;

		if ($collapsible && ($this->y == $this->tMargin) && (!$this->ColActive)) {
			$h = 0;
		}

		if (is_string($h)) {
			$this->y += $this->lasth;
		} else {
			$this->y += $h;
		}
	}

	/* -- HTML-CSS -- */

	function DivLn($h, $level = -3, $move_y = true, $collapsible = false, $state = 0)
	{
		// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
		// Used in Columns and keep-with-table i.e. "kwt"
		// writes background block by block so it can be repositioned
		// and also used in writingFlowingBlock at top and bottom of blocks to move y (not to draw/paint anything)
		// adds lines (y) where DIV bgcolors are filled in
		// this->x is returned as it was
		// allows .00001 as nominal height used for bookmarks/annotations etc.
		if ($collapsible && (sprintf("%0.4f", $this->y) == sprintf("%0.4f", $this->tMargin)) && (!$this->ColActive)) {
			return;
		}

		// mPDF 6 Columns
		//   if ($collapsible && (sprintf("%0.4f", $this->y)==sprintf("%0.4f", $this->y0)) && ($this->ColActive) && $this->CurrCol == 0) { return; }	// *COLUMNS*
		if ($collapsible && (sprintf("%0.4f", $this->y) == sprintf("%0.4f", $this->y0)) && ($this->ColActive)) {
			return;
		} // *COLUMNS*
		// Still use this method if columns or keep-with-table, as it allows repositioning later
		// otherwise, now uses PaintDivBB()
		if (!$this->ColActive && !$this->kwt) {
			if ($move_y && !$this->ColActive) {
				$this->y += $h;
			}
			return;
		}

		if ($level == -3) {
			$level = $this->blklvl;
		}
		$firstblockfill = $this->GetFirstBlockFill();
		if ($firstblockfill && $this->blklvl > 0 && $this->blklvl >= $firstblockfill) {
			$last_x = 0;
			$last_w = 0;
			$last_fc = $this->FillColor;
			$bak_x = $this->x;
			$bak_h = $this->divheight;
			$this->divheight = 0; // Temporarily turn off divheight - as Cell() uses it to check for PageBreak
			for ($blvl = $firstblockfill; $blvl <= $level; $blvl++) {
				$this->x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
				// mPDF 6
				if ($this->blk[$blvl]['bgcolor']) {
					$this->SetFColor($this->blk[$blvl]['bgcolorarray']);
				}
				if ($last_x != ($this->lMargin + $this->blk[$blvl]['outer_left_margin']) || ($last_w != $this->blk[$blvl]['width']) || $last_fc != $this->FillColor || (isset($this->blk[$blvl]['border_top']['s']) && $this->blk[$blvl]['border_top']['s']) || (isset($this->blk[$blvl]['border_bottom']['s']) && $this->blk[$blvl]['border_bottom']['s']) || (isset($this->blk[$blvl]['border_left']['s']) && $this->blk[$blvl]['border_left']['s']) || (isset($this->blk[$blvl]['border_right']['s']) && $this->blk[$blvl]['border_right']['s'])) {
					$x = $this->x;
					$this->Cell(($this->blk[$blvl]['width']), $h, '', '', 0, '', 1);
					$this->x = $x;
					if (!$this->keep_block_together && !$this->writingHTMLheader && !$this->writingHTMLfooter) {
						// $state = 0 normal; 1 top; 2 bottom; 3 top and bottom
						if ($blvl == $this->blklvl) {
							$this->PaintDivLnBorder($state, $blvl, $h);
						} else {
							$this->PaintDivLnBorder(0, $blvl, $h);
						}
					}
				}
				$last_x = $this->lMargin + $this->blk[$blvl]['outer_left_margin'];
				$last_w = $this->blk[$blvl]['width'];
				$last_fc = $this->FillColor;
			}
			// Reset current block fill
			if (isset($this->blk[$this->blklvl]['bgcolorarray'])) {
				$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
				$this->SetFColor($bcor);
			}
			$this->x = $bak_x;
			$this->divheight = $bak_h;
		}
		if ($move_y) {
			$this->y += $h;
		}
	}

	/* -- END HTML-CSS -- */

	function SetX($x)
	{
		// Set x position
		if ($x >= 0) {
			$this->x = $x;
		} else {
			$this->x = $this->w + $x;
		}
	}

	function SetY($y)
	{
		// Set y position and reset x
		$this->x = $this->lMargin;
		if ($y >= 0) {
			$this->y = $y;
		} else {
			$this->y = $this->h + $y;
		}
	}

	function SetXY($x, $y)
	{
		// Set x and y positions
		$this->SetY($y);
		$this->SetX($x);
	}

	function Output($name = '', $dest = '')
	{
		$this->logger->debug(sprintf('PDF generated in %.6F seconds', microtime(true) - $this->time0), ['context' => LogContext::STATISTICS]);

		// Finish document if necessary
		if ($this->state < 3) {
			$this->Close();
		}

		if ($this->debug && error_get_last()) {
			$e = error_get_last();
			if (($e['type'] < 2048 && $e['type'] != 8) || (intval($e['type']) & intval(ini_get("error_reporting")))) {
				throw new \Mpdf\MpdfException(
					sprintf('Error detected. PDF file generation aborted: %s', $e['message']),
					$e['type'],
					1,
					$e['file'],
					$e['line']
				);
			}
		}

		if (($this->PDFA || $this->PDFX) && $this->encrypted) {
			throw new \Mpdf\MpdfException('PDF/A1-b or PDF/X1-a does not permit encryption of documents.');
		}

		if (count($this->PDFAXwarnings) && (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto))) {
			if ($this->PDFA) {
				$standard = 'PDFA/1-b';
				$option = '$mpdf->PDFAauto';
			} else {
				$standard = 'PDFX/1-a ';
				$option = '$mpdf->PDFXauto';
			}

			$this->logger->warning(sprintf('PDF could not be generated as it stands as a %s compliant file.', $standard), ['context' => LogContext::PDFA_PDFX]);
			$this->logger->warning(sprintf('These issues can be automatically fixed by mPDF using %s = true;', $option), ['context' => LogContext::PDFA_PDFX]);
			$this->logger->warning(sprintf('Action that mPDF will take to automatically force %s compliance are shown further in the log.', $standard), ['context' => LogContext::PDFA_PDFX]);

			$this->PDFAXwarnings = array_unique($this->PDFAXwarnings);
			foreach ($this->PDFAXwarnings as $w) {
				$this->logger->warning($w, ['context' => LogContext::PDFA_PDFX]);
			}

			throw new \Mpdf\MpdfException('PDFA/PDFX warnings generated. See log for further details');
		}

		$this->logger->debug(sprintf('Compiled in %.6F seconds', microtime(true) - $this->time0), ['context' => LogContext::STATISTICS]);
		$this->logger->debug(sprintf('Peak Memory usage %s MB', number_format(memory_get_peak_usage(true) / (1024 * 1024), 2)), ['context' => LogContext::STATISTICS]);
		$this->logger->debug(sprintf('PDF file size %s kB', number_format(strlen($this->buffer) / 1024)), ['context' => LogContext::STATISTICS]);
		$this->logger->debug(sprintf('%d fonts used', count($this->fonts)), ['context' => LogContext::STATISTICS]);

		if (is_bool($dest)) {
			$dest = $dest ? Destination::DOWNLOAD : Destination::FILE;
		}

		$dest = strtoupper($dest);
		if (empty($dest)) {
			if (empty($name)) {
				$name = 'mpdf.pdf';
				$dest = Destination::INLINE;
			} else {
				$dest = Destination::FILE;
			}
		}

		switch ($dest) {

			case Destination::INLINE:

				if (headers_sent($filename, $line)) {
					throw new \Mpdf\MpdfException(
						sprintf('Data has already been sent to output (%s at line %s), unable to output PDF file', $filename, $line)
					);
				}

				if ($this->debug && !$this->allow_output_buffering && ob_get_contents()) {
					throw new \Mpdf\MpdfException('Output has already been sent from the script - PDF file generation aborted.');
				}

				// We send to a browser
				if (PHP_SAPI !== 'cli') {
					header('Content-Type: application/pdf');

					if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
						// don't use length if server using compression
						header('Content-Length: ' . strlen($this->buffer));
					}

					header('Content-disposition: inline; filename="' . $name . '"');
					header('Cache-Control: public, must-revalidate, max-age=0');
					header('Pragma: public');
					header('X-Generator: mPDF' . ($this->exposeVersion ? (' ' . static::VERSION) : ''));
					header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
					header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				}

				echo $this->buffer;

				break;

			case Destination::DOWNLOAD:

				if (headers_sent()) {
					throw new \Mpdf\MpdfException('Data has already been sent to output, unable to output PDF file');
				}

				header('Content-Description: File Transfer');
				header('Content-Transfer-Encoding: binary');
				header('Cache-Control: public, must-revalidate, max-age=0');
				header('Pragma: public');
				header('X-Generator: mPDF' . ($this->exposeVersion ? (' ' . static::VERSION) : ''));
				header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
				header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
				header('Content-Type: application/pdf');

				if (!isset($_SERVER['HTTP_ACCEPT_ENCODING']) || empty($_SERVER['HTTP_ACCEPT_ENCODING'])) {
					// don't use length if server using compression
					header('Content-Length: ' . strlen($this->buffer));
				}

				header('Content-Disposition: attachment; filename="' . $name . '"');

				echo $this->buffer;

				break;

			case Destination::FILE:
				$f = fopen($name, 'wb');

				if (!$f) {
					throw new \Mpdf\MpdfException(sprintf('Unable to create output file %s', $name));
				}

				fwrite($f, $this->buffer, strlen($this->buffer));
				fclose($f);

				break;

			case Destination::STRING_RETURN:
				$this->cache->clearOld();
				return $this->buffer;

			default:
				throw new \Mpdf\MpdfException(sprintf('Incorrect output destination %s', $dest));
		}

		$this->cache->clearOld();
	}

	public function OutputBinaryData()
	{
		return $this->Output(null, Destination::STRING_RETURN);
	}

	public function OutputHttpInline()
	{
		return $this->Output(null, Destination::INLINE);
	}

	/**
	 * @param string $fileName
	 */
	public function OutputHttpDownload($fileName)
	{
		return $this->Output($fileName, Destination::DOWNLOAD);
	}

	/**
	 * @param string $fileName
	 */
	public function OutputFile($fileName)
	{
		return $this->Output($fileName, Destination::FILE);
	}

	// *****************************************************************************
	//                                                                             *
	//                             Protected methods                               *
	//                                                                             *
	// *****************************************************************************
	function _dochecks()
	{
		// Check for locale-related bug
		if (1.1 == 1) {
			throw new \Mpdf\MpdfException('Do not alter the locale before including mPDF');
		}

		// Check for decimal separator
		if (sprintf('%.1f', 1.0) != '1.0') {
			setlocale(LC_NUMERIC, 'C');
		}

		if (ini_get('mbstring.func_overload')) {
			throw new \Mpdf\MpdfException('Mpdf cannot function properly with mbstring.func_overload enabled');
		}

		if (!function_exists('mb_substr')) {
			throw new \Mpdf\MpdfException('mbstring extension must be loaded in order to run mPDF');
		}

		if (!function_exists('mb_regex_encoding')) {
			$mamp = '';
			if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
				$mamp = ' If using MAMP, there is a bug in its PHP build causing this.';
			}

			throw new \Mpdf\MpdfException('mbstring extension with mbregex support must be loaded in order to run mPDF.' . $mamp);
		}
	}

	function _puthtmlheaders()
	{
		$this->state = 2;
		$nb = $this->page;
		for ($n = 1; $n <= $nb; $n++) {
			if ($this->mirrorMargins && $n % 2 == 0) {
				$OE = 'E';
			} // EVEN
			else {
				$OE = 'O';
			}
			$this->page = $n;
			$pn = $this->docPageNum($n);
			if ($pn) {
				$pnstr = $this->pagenumPrefix . $pn . $this->pagenumSuffix;
			} else {
				$pnstr = '';
			}

			$pnt = $this->docPageNumTotal($n);

			if ($pnt) {
				$pntstr = $this->nbpgPrefix . $pnt . $this->nbpgSuffix;
			} else {
				$pntstr = '';
			}

			if (isset($this->saveHTMLHeader[$n][$OE])) {
				$html = isset($this->saveHTMLHeader[$n][$OE]['html']) ? $this->saveHTMLHeader[$n][$OE]['html'] : '';
				$this->lMargin = $this->saveHTMLHeader[$n][$OE]['ml'];
				$this->rMargin = $this->saveHTMLHeader[$n][$OE]['mr'];
				$this->tMargin = $this->saveHTMLHeader[$n][$OE]['mh'];
				$this->bMargin = $this->saveHTMLHeader[$n][$OE]['mf'];
				$this->margin_header = $this->saveHTMLHeader[$n][$OE]['mh'];
				$this->margin_footer = $this->saveHTMLHeader[$n][$OE]['mf'];
				$this->w = $this->saveHTMLHeader[$n][$OE]['pw'];
				$this->h = $this->saveHTMLHeader[$n][$OE]['ph'];
				if ($this->w > $this->h) {
					$this->hPt = $this->fwPt;
					$this->wPt = $this->fhPt;
				} else {
					$this->hPt = $this->fhPt;
					$this->wPt = $this->fwPt;
				}
				$rotate = (isset($this->saveHTMLHeader[$n][$OE]['rotate']) ? $this->saveHTMLHeader[$n][$OE]['rotate'] : null);
				$this->Reset();
				$this->pageoutput[$n] = [];
				$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
				$this->x = $this->lMargin;
				$this->y = $this->margin_header;

				// Replace of page number aliases and date format
				$html = $this->aliasReplace($html, $pnstr, $pntstr, $nb);

				$this->HTMLheaderPageLinks = [];
				$this->HTMLheaderPageAnnots = [];
				$this->HTMLheaderPageForms = [];
				$this->pageBackgrounds = [];

				$this->writingHTMLheader = true;
				$this->WriteHTML($html, HTMLParserMode::HTML_HEADER_BUFFER);
				$this->writingHTMLheader = false;
				$this->Reset();
				$this->pageoutput[$n] = [];

				$s = $this->PrintPageBackgrounds();
				$this->headerbuffer = $s . $this->headerbuffer;
				$os = '';
				if ($rotate) {
					$os .= sprintf('q 0 -1 1 0 0 %.3F cm ', ($this->w * Mpdf::SCALE));
					// To rotate the other way i.e. Header to left of page:
					// $os .= sprintf('q 0 1 -1 0 %.3F %.3F cm ',($this->h*Mpdf::SCALE), (($this->rMargin - $this->lMargin )*Mpdf::SCALE));
				}
				$os .= $this->headerbuffer;
				if ($rotate) {
					$os .= ' Q' . "\n";
				}

				// Writes over the page background but behind any other output on page
				$os = preg_replace(['/\\\\/', '/\$/'], ['\\\\\\\\', '\\\\$'], $os);

				$this->pages[$n] = preg_replace('/(___HEADER___MARKER' . $this->uniqstr . ')/', "\n" . $os . "\n" . '\\1', $this->pages[$n]);

				$lks = $this->HTMLheaderPageLinks;
				foreach ($lks as $lk) {
					if ($rotate) {
						$lw = $lk[2];
						$lh = $lk[3];
						$lk[2] = $lh;
						$lk[3] = $lw; // swap width and height
						$ax = $lk[0] / Mpdf::SCALE;
						$ay = $lk[1] / Mpdf::SCALE;
						$bx = $ay - ($lh / Mpdf::SCALE);
						$by = $this->w - $ax;
						$lk[0] = $bx * Mpdf::SCALE;
						$lk[1] = ($this->h - $by) * Mpdf::SCALE - $lw;
					}
					$this->PageLinks[$n][] = $lk;
				}
				/* -- FORMS -- */
				foreach ($this->HTMLheaderPageForms as $f) {
					$this->form->forms[$f['n']] = $f;
				}
				/* -- END FORMS -- */
			}

			if (isset($this->saveHTMLFooter[$n][$OE])) {

				$html = $this->saveHTMLFooter[$this->page][$OE]['html'];

				$this->lMargin = $this->saveHTMLFooter[$n][$OE]['ml'];
				$this->rMargin = $this->saveHTMLFooter[$n][$OE]['mr'];
				$this->tMargin = $this->saveHTMLFooter[$n][$OE]['mh'];
				$this->bMargin = $this->saveHTMLFooter[$n][$OE]['mf'];
				$this->margin_header = $this->saveHTMLFooter[$n][$OE]['mh'];
				$this->margin_footer = $this->saveHTMLFooter[$n][$OE]['mf'];
				$this->w = $this->saveHTMLFooter[$n][$OE]['pw'];
				$this->h = $this->saveHTMLFooter[$n][$OE]['ph'];
				if ($this->w > $this->h) {
					$this->hPt = $this->fwPt;
					$this->wPt = $this->fhPt;
				} else {
					$this->hPt = $this->fhPt;
					$this->wPt = $this->fwPt;
				}
				$rotate = (isset($this->saveHTMLFooter[$n][$OE]['rotate']) ? $this->saveHTMLFooter[$n][$OE]['rotate'] : null);
				$this->Reset();
				$this->pageoutput[$n] = [];
				$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
				$this->x = $this->lMargin;
				$top_y = $this->y = $this->h - $this->margin_footer;

				// if bottom-margin==0, corrects to avoid division by zero
				if ($this->y == $this->h) {
					$top_y = $this->y = ($this->h + 0.01);
				}

				// Replace of page number aliases and date format
				$html = $this->aliasReplace($html, $pnstr, $pntstr, $nb);

				$this->HTMLheaderPageLinks = [];
				$this->HTMLheaderPageAnnots = [];
				$this->HTMLheaderPageForms = [];
				$this->pageBackgrounds = [];

				$this->writingHTMLfooter = true;
				$this->InFooter = true;
				$this->WriteHTML($html, HTMLParserMode::HTML_HEADER_BUFFER);
				$this->InFooter = false;
				$this->Reset();
				$this->pageoutput[$n] = [];

				$fheight = $this->y - $top_y;
				$adj = -$fheight;

				$s = $this->PrintPageBackgrounds(-$adj);
				$this->headerbuffer = $s . $this->headerbuffer;
				$this->writingHTMLfooter = false; // mPDF 5.7.3  (moved after PrintPageBackgrounds so can adjust position of images in footer)

				$os = '';
				$os .= $this->StartTransform(true) . "\n";

				if ($rotate) {
					$os .= sprintf('q 0 -1 1 0 0 %.3F cm ', ($this->w * Mpdf::SCALE));
					// To rotate the other way i.e. Header to left of page:
					// $os .= sprintf('q 0 1 -1 0 %.3F %.3F cm ',($this->h*Mpdf::SCALE), (($this->rMargin - $this->lMargin )*Mpdf::SCALE));
				}

				$os .= $this->transformTranslate(0, $adj, true) . "\n";
				$os .= $this->headerbuffer;

				if ($rotate) {
					$os .= ' Q' . "\n";
				}

				$os .= $this->StopTransform(true) . "\n";

				// Writes over the page background but behind any other output on page
				$os = preg_replace(['/\\\\/', '/\$/'], ['\\\\\\\\', '\\\\$'], $os);

				$this->pages[$n] = preg_replace('/(___HEADER___MARKER' . $this->uniqstr . ')/', "\n" . $os . "\n" . '\\1', $this->pages[$n]);

				$lks = $this->HTMLheaderPageLinks;

				foreach ($lks as $lk) {

					$lk[1] -= $adj * Mpdf::SCALE;

					if ($rotate) {
						$lw = $lk[2];
						$lh = $lk[3];
						$lk[2] = $lh;
						$lk[3] = $lw; // swap width and height

						$ax = $lk[0] / Mpdf::SCALE;
						$ay = $lk[1] / Mpdf::SCALE;
						$bx = $ay - ($lh / Mpdf::SCALE);
						$by = $this->w - $ax;
						$lk[0] = $bx * Mpdf::SCALE;
						$lk[1] = ($this->h - $by) * Mpdf::SCALE - $lw;
					}

					$this->PageLinks[$n][] = $lk;
				}

				/* -- FORMS -- */
				foreach ($this->HTMLheaderPageForms as $f) {
					$f['y'] += $adj;
					$this->form->forms[$f['n']] = $f;
				}
				/* -- END FORMS -- */
			}

			// Customization for https://github.com/mpdf/mpdf/issues/172
			// Replace of page number aliases and date format
			$this->pages[$n] = $this->aliasReplace($this->pages[$n], $pnstr, $pntstr, $nb);
		}

		$this->page = $nb;
		$this->state = 1;
	}

	/* -- ANNOTATIONS -- */
	function Annotation($text, $x = 0, $y = 0, $icon = 'Note', $author = '', $subject = '', $opacity = 0, $colarray = false, $popup = '', $file = '')
	{
		if (is_array($colarray) && count($colarray) == 3) {
			$colarray = $this->colorConverter->convert('rgb(' . $colarray[0] . ',' . $colarray[1] . ',' . $colarray[2] . ')', $this->PDFAXwarnings);
		}
		if ($colarray === false) {
			$colarray = $this->colorConverter->convert('yellow', $this->PDFAXwarnings);
		}
		if ($x == 0) {
			$x = $this->x;
		}
		if ($y == 0) {
			$y = $this->y;
		}
		$page = $this->page;
		if ($page < 1) { // Document has not been started - assume it's for first page
			$page = 1;
			if ($x == 0) {
				$x = $this->lMargin;
			}
			if ($y == 0) {
				$y = $this->tMargin;
			}
		}

		if ($this->PDFA || $this->PDFX) {
			if (($this->PDFA && !$this->PDFAauto) || ($this->PDFX && !$this->PDFXauto)) {
				$this->PDFAXwarnings[] = "Annotation markers cannot be semi-transparent in PDFA1-b or PDFX/1-a, so they may make underlying text unreadable. (Annotation markers moved to right margin)";
			}
			$x = ($this->w) - $this->rMargin * 0.66;
		}
		if (!$this->annotMargin) {
			$y -= $this->FontSize / 2;
		}

		if (!$opacity && $this->annotMargin) {
			$opacity = 1;
		} elseif (!$opacity) {
			$opacity = $this->annotOpacity;
		}

		$an = ['txt' => $text, 'x' => $x, 'y' => $y, 'opt' => ['Icon' => $icon, 'T' => $author, 'Subj' => $subject, 'C' => $colarray, 'CA' => $opacity, 'popup' => $popup, 'file' => $file]];

		if ($this->keep_block_together) { // don't write yet
			return;
		} elseif ($this->table_rotate) {
			$this->tbrot_Annots[$this->page][] = $an;
			return;
		} elseif ($this->kwt) {
			$this->kwt_Annots[$this->page][] = $an;
			return;
		}

		if ($this->writingHTMLheader || $this->writingHTMLfooter) {
			$this->HTMLheaderPageAnnots[] = $an;
			return;
		}

		// Put an Annotation on the page
		$this->PageAnnots[$page][] = $an;

		/* -- COLUMNS -- */
		// Save cross-reference to Column buffer
		$ref = isset($this->PageAnnots[$this->page]) ? (count($this->PageAnnots[$this->page]) - 1) : -1;
		$this->columnAnnots[$this->CurrCol][intval($this->x)][intval($this->y)] = $ref;
		/* -- END COLUMNS -- */
	}

	/* -- END ANNOTATIONS -- */

	function _enddoc()
	{
		// @log Writing Headers & Footers

		$this->_puthtmlheaders();

		// @log Writing Pages

		// Remove references to unused fonts (usually default font)
		foreach ($this->fonts as $fk => $font) {
			if (isset($font['type']) && $font['type'] == 'TTF' && !$font['used']) {
				if ($font['sip'] || $font['smp']) {
					foreach ($font['subsetfontids'] as $k => $fid) {
						foreach ($this->pages as $pn => $page) {
							$this->pages[$pn] = preg_replace('/\s\/F' . $fid . ' \d[\d.]* Tf\s/is', ' ', $this->pages[$pn]);
						}
					}
				} else {
					foreach ($this->pages as $pn => $page) {
						$this->pages[$pn] = preg_replace('/\s\/F' . $font['i'] . ' \d[\d.]* Tf\s/is', ' ', $this->pages[$pn]);
					}
				}
			}
		}

		if (count($this->layers)) {
			foreach ($this->pages as $pn => $page) {
				preg_match_all('/\/OCZ-index \/ZI(\d+) BDC(.*?)(EMCZ)-index/is', $this->pages[$pn], $m1);
				preg_match_all('/\/OCBZ-index \/ZI(\d+) BDC(.*?)(EMCBZ)-index/is', $this->pages[$pn], $m2);
				preg_match_all('/\/OCGZ-index \/ZI(\d+) BDC(.*?)(EMCGZ)-index/is', $this->pages[$pn], $m3);
				$m = [];
				for ($i = 0; $i < 4; $i++) {
					$m[$i] = array_merge($m1[$i], $m2[$i], $m3[$i]);
				}
				if (count($m[0])) {
					$sortarr = [];
					for ($i = 0; $i < count($m[0]); $i++) {
						$key = $m[1][$i] * 2;
						if ($m[3][$i] == 'EMCZ') {
							$key +=2; // background first then gradient then normal
						} elseif ($m[3][$i] == 'EMCGZ') {
							$key +=1;
						}
						$sortarr[$i] = $key;
					}
					asort($sortarr);
					foreach ($sortarr as $i => $k) {
						$this->pages[$pn] = str_replace($m[0][$i], '', $this->pages[$pn]);
						$this->pages[$pn] .= "\n" . $m[0][$i] . "\n";
					}
					$this->pages[$pn] = preg_replace('/\/OC[BG]{0,1}Z-index \/ZI(\d+) BDC/is', '/OC /ZI\\1 BDC ', $this->pages[$pn]);
					$this->pages[$pn] = preg_replace('/EMC[BG]{0,1}Z-index/is', 'EMC', $this->pages[$pn]);
				}
			}
		}

		$this->pageWriter->writePages();

		// @log Writing document resources

		$this->resourceWriter->writeResources();

		// Info
		$this->writer->object();
		$this->InfoRoot = $this->n;
		$this->writer->write('<<');

		// @log Writing document info
		$this->metadataWriter->writeInfo();

		$this->writer->write('>>');
		$this->writer->write('endobj');

		// METADATA
		if ($this->PDFA || $this->PDFX) {
			$this->metadataWriter->writeMetadata();
		}

		// OUTPUTINTENT
		if ($this->PDFA || $this->PDFX || $this->ICCProfile) {
			$this->metadataWriter->writeOutputIntent();
		}

		// Associated files
		if ($this->associatedFiles) {
			$this->metadataWriter->writeAssociatedFiles();
		}

		// Catalog
		$this->writer->object();
		$this->writer->write('<<');

		// @log Writing document catalog

		$this->metadataWriter->writeCatalog();

		$this->writer->write('>>');
		$this->writer->write('endobj');

		// Cross-ref
		$o = strlen($this->buffer);
		$this->writer->write('xref');
		$this->writer->write('0 ' . ($this->n + 1));
		$this->writer->write('0000000000 65535 f ');

		for ($i = 1; $i <= $this->n; $i++) {
			$this->writer->write(sprintf('%010d 00000 n ', $this->offsets[$i]));
		}

		// Trailer
		$this->writer->write('trailer');
		$this->writer->write('<<');

		$this->metadataWriter->writeTrailer();

		$this->writer->write('>>');
		$this->writer->write('startxref');
		$this->writer->write($o);

		$this->buffer .= '%%EOF';
		$this->state = 3;
	}

	function _beginpage(
		$orientation,
		$mgl = '',
		$mgr = '',
		$mgt = '',
		$mgb = '',
		$mgh = '',
		$mgf = '',
		$ohname = '',
		$ehname = '',
		$ofname = '',
		$efname = '',
		$ohvalue = 0,
		$ehvalue = 0,
		$ofvalue = 0,
		$efvalue = 0,
		$pagesel = '',
		$newformat = ''
	) {
		if (!($pagesel && $this->page == 1 && (sprintf("%0.4f", $this->y) == sprintf("%0.4f", $this->tMargin)))) {
			$this->page++;
			$this->pages[$this->page] = '';
		}

		$this->state = 2;
		$resetHTMLHeadersrequired = false;

		if ($newformat) {
			$this->_setPageSize($newformat, $orientation);
		}

		/* -- CSS-PAGE -- */
		// Paged media (page-box)
		if ($pagesel || $this->page_box['using']) {

			if ($pagesel || $this->page === 1) {
				$first = true;
			} else {
				$first = false;
			}

			if ($this->mirrorMargins && ($this->page % 2 === 0)) {
				$oddEven = 'E';
			} else {
				$oddEven = 'O';
			}

			if ($pagesel) {
				$psel = $pagesel;
			} elseif ($this->page_box['current']) {
				$psel = $this->page_box['current'];
			} else {
				$psel = '';
			}

			list($orientation, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $hname, $fname, $bg, $resetpagenum, $pagenumstyle, $suppress, $marks, $newformat) = $this->SetPagedMediaCSS($psel, $first, $oddEven);

			if ($this->mirrorMargins && ($this->page % 2 === 0)) {

				if ($hname) {
					$ehvalue = 1;
					$ehname = $hname;
				} else {
					$ehvalue = -1;
				}

				if ($fname) {
					$efvalue = 1;
					$efname = $fname;
				} else {
					$efvalue = -1;
				}

			} else {

				if ($hname) {
					$ohvalue = 1;
					$ohname = $hname;
				} else {
					$ohvalue = -1;
				}

				if ($fname) {
					$ofvalue = 1;
					$ofname = $fname;
				} else {
					$ofvalue = -1;
				}
			}

			if ($resetpagenum || $pagenumstyle || $suppress) {
				$this->PageNumSubstitutions[] = ['from' => ($this->page), 'reset' => $resetpagenum, 'type' => $pagenumstyle, 'suppress' => $suppress];
			}

			// PAGED MEDIA - CROP / CROSS MARKS from @PAGE
			$this->show_marks = $marks;

			// Background color
			if (isset($bg['BACKGROUND-COLOR'])) {
				$cor = $this->colorConverter->convert($bg['BACKGROUND-COLOR'], $this->PDFAXwarnings);
				if ($cor) {
					$this->bodyBackgroundColor = $cor;
				}
			} else {
				$this->bodyBackgroundColor = false;
			}

			/* -- BACKGROUNDS -- */
			if (isset($bg['BACKGROUND-GRADIENT'])) {
				$this->bodyBackgroundGradient = $bg['BACKGROUND-GRADIENT'];
			} else {
				$this->bodyBackgroundGradient = false;
			}

			// Tiling Patterns
			if (isset($bg['BACKGROUND-IMAGE']) && $bg['BACKGROUND-IMAGE']) {
				$ret = $this->SetBackground($bg, $this->pgwidth);
				if ($ret) {
					$this->bodyBackgroundImage = $ret;
				}
			} else {
				$this->bodyBackgroundImage = false;
			}
			/* -- END BACKGROUNDS -- */

			$this->page_box['current'] = $psel;
			$this->page_box['using'] = true;
		}
		/* -- END CSS-PAGE -- */

		// Page orientation
		if (!$orientation) {
			$orientation = $this->DefOrientation;
		} else {
			$orientation = strtoupper(substr($orientation, 0, 1));
			if ($orientation != $this->DefOrientation) {
				$this->OrientationChanges[$this->page] = true;
			}
		}

		if ($orientation !== $this->CurOrientation || $newformat) {

			// Change orientation
			if ($orientation === 'P') {
				$this->wPt = $this->fwPt;
				$this->hPt = $this->fhPt;
				$this->w = $this->fw;
				$this->h = $this->fh;
				if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation === 'P') {
					$this->tMargin = $this->orig_tMargin;
					$this->bMargin = $this->orig_bMargin;
					$this->DeflMargin = $this->orig_lMargin;
					$this->DefrMargin = $this->orig_rMargin;
					$this->margin_header = $this->orig_hMargin;
					$this->margin_footer = $this->orig_fMargin;
				} else {
					$resetHTMLHeadersrequired = true;
				}
			} else {
				$this->wPt = $this->fhPt;
				$this->hPt = $this->fwPt;
				$this->w = $this->fh;
				$this->h = $this->fw;

				if (($this->forcePortraitHeaders || $this->forcePortraitMargins) && $this->DefOrientation === 'P') {
					$this->tMargin = $this->orig_lMargin;
					$this->bMargin = $this->orig_rMargin;
					$this->DeflMargin = $this->orig_bMargin;
					$this->DefrMargin = $this->orig_tMargin;
					$this->margin_header = $this->orig_hMargin;
					$this->margin_footer = $this->orig_fMargin;
				} else {
					$resetHTMLHeadersrequired = true;
				}
			}

			$this->CurOrientation = $orientation;
			$this->ResetMargins();
			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
			$this->PageBreakTrigger = $this->h - $this->bMargin;
		}

		$this->pageDim[$this->page]['w'] = $this->w;
		$this->pageDim[$this->page]['h'] = $this->h;

		$this->pageDim[$this->page]['outer_width_LR'] = $this->page_box['outer_width_LR'] ?: 0;
		$this->pageDim[$this->page]['outer_width_TB'] = $this->page_box['outer_width_TB'] ?: 0;

		if (!$this->page_box['outer_width_LR'] && !$this->page_box['outer_width_TB']) {
			$this->pageDim[$this->page]['bleedMargin'] = 0;
		} elseif ($this->bleedMargin <= $this->page_box['outer_width_LR'] && $this->bleedMargin <= $this->page_box['outer_width_TB']) {
			$this->pageDim[$this->page]['bleedMargin'] = $this->bleedMargin;
		} else {
			$this->pageDim[$this->page]['bleedMargin'] = min($this->page_box['outer_width_LR'], $this->page_box['outer_width_TB']) - 0.01;
		}

		// If Page Margins are re-defined
		// strlen()>0 is used to pick up (integer) 0, (string) '0', or set value
		if ((strlen($mgl) > 0 && $this->DeflMargin != $mgl) || (strlen($mgr) > 0 && $this->DefrMargin != $mgr) || (strlen($mgt) > 0 && $this->tMargin != $mgt) || (strlen($mgb) > 0 && $this->bMargin != $mgb) || (strlen($mgh) > 0 && $this->margin_header != $mgh) || (strlen($mgf) > 0 && $this->margin_footer != $mgf)) {

			if (strlen($mgl) > 0) {
				$this->DeflMargin = $mgl;
			}

			if (strlen($mgr) > 0) {
				$this->DefrMargin = $mgr;
			}

			if (strlen($mgt) > 0) {
				$this->tMargin = $mgt;
			}

			if (strlen($mgb) > 0) {
				$this->bMargin = $mgb;
			}

			if (strlen($mgh) > 0) {
				$this->margin_header = $mgh;
			}

			if (strlen($mgf) > 0) {
				$this->margin_footer = $mgf;
			}

			$this->ResetMargins();
			$this->SetAutoPageBreak($this->autoPageBreak, $this->bMargin);

			$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
			$resetHTMLHeadersrequired = true;
		}

		$this->ResetMargins();
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->SetAutoPageBreak($this->autoPageBreak, $this->bMargin);

		// Reset column top margin
		$this->y0 = $this->tMargin;

		$this->x = $this->lMargin;
		$this->y = $this->tMargin;
		$this->FontFamily = '';

		// HEADERS AND FOOTERS	// mPDF 6
		if ($ohvalue < 0 || strtoupper($ohvalue) == 'OFF') {
			$this->HTMLHeader = '';
			$resetHTMLHeadersrequired = true;
		} elseif ($ohname && $ohvalue > 0) {
			if (preg_match('/^html_(.*)$/i', $ohname, $n)) {
				$name = $n[1];
			} else {
				$name = $ohname;
			}
			if (isset($this->pageHTMLheaders[$name])) {
				$this->HTMLHeader = $this->pageHTMLheaders[$name];
			} else {
				$this->HTMLHeader = '';
			}
			$resetHTMLHeadersrequired = true;
		}

		if ($ehvalue < 0 || strtoupper($ehvalue) == 'OFF') {
			$this->HTMLHeaderE = '';
			$resetHTMLHeadersrequired = true;
		} elseif ($ehname && $ehvalue > 0) {
			if (preg_match('/^html_(.*)$/i', $ehname, $n)) {
				$name = $n[1];
			} else {
				$name = $ehname;
			}
			if (isset($this->pageHTMLheaders[$name])) {
				$this->HTMLHeaderE = $this->pageHTMLheaders[$name];
			} else {
				$this->HTMLHeaderE = '';
			}
			$resetHTMLHeadersrequired = true;
		}

		if ($ofvalue < 0 || strtoupper($ofvalue) == 'OFF') {
			$this->HTMLFooter = '';
			$resetHTMLHeadersrequired = true;
		} elseif ($ofname && $ofvalue > 0) {
			if (preg_match('/^html_(.*)$/i', $ofname, $n)) {
				$name = $n[1];
			} else {
				$name = $ofname;
			}
			if (isset($this->pageHTMLfooters[$name])) {
				$this->HTMLFooter = $this->pageHTMLfooters[$name];
			} else {
				$this->HTMLFooter = '';
			}
			$resetHTMLHeadersrequired = true;
		}

		if ($efvalue < 0 || strtoupper($efvalue) == 'OFF') {
			$this->HTMLFooterE = '';
			$resetHTMLHeadersrequired = true;
		} elseif ($efname && $efvalue > 0) {
			if (preg_match('/^html_(.*)$/i', $efname, $n)) {
				$name = $n[1];
			} else {
				$name = $efname;
			}
			if (isset($this->pageHTMLfooters[$name])) {
				$this->HTMLFooterE = $this->pageHTMLfooters[$name];
			} else {
				$this->HTMLFooterE = '';
			}
			$resetHTMLHeadersrequired = true;
		}

		if ($resetHTMLHeadersrequired) {
			$this->SetHTMLHeader($this->HTMLHeader);
			$this->SetHTMLHeader($this->HTMLHeaderE, 'E');
			$this->SetHTMLFooter($this->HTMLFooter);
			$this->SetHTMLFooter($this->HTMLFooterE, 'E');
		}


		if (($this->mirrorMargins) && (($this->page) % 2 == 0)) { // EVEN
			$this->_setAutoHeaderHeight($this->HTMLHeaderE);
			$this->_setAutoFooterHeight($this->HTMLFooterE);
		} else { // ODD or DEFAULT
			$this->_setAutoHeaderHeight($this->HTMLHeader);
			$this->_setAutoFooterHeight($this->HTMLFooter);
		}

		// Reset column top margin
		$this->y0 = $this->tMargin;

		$this->x = $this->lMargin;
		$this->y = $this->tMargin;
	}

	// mPDF 6
	function _setAutoHeaderHeight(&$htmlh)
	{
		/* When the setAutoTopMargin option is set to pad/stretch, only apply auto header height when a header exists */
		if ($this->HTMLHeader === '' && $this->HTMLHeaderE === '') {
			return;
		}

		if ($this->setAutoTopMargin == 'pad') {
			if (isset($htmlh['h']) && $htmlh['h']) {
				$h = $htmlh['h'];
			} // 5.7.3
			else {
				$h = 0;
			}
			$this->tMargin = $this->margin_header + $h + $this->orig_tMargin;
		} elseif ($this->setAutoTopMargin == 'stretch') {
			if (isset($htmlh['h']) && $htmlh['h']) {
				$h = $htmlh['h'];
			} // 5.7.3
			else {
				$h = 0;
			}
			$this->tMargin = max($this->orig_tMargin, $this->margin_header + $h + $this->autoMarginPadding);
		}
	}

	// mPDF 6
	function _setAutoFooterHeight(&$htmlf)
	{
		/* When the setAutoTopMargin option is set to pad/stretch, only apply auto footer height when a footer exists */
		if ($this->HTMLFooter === '' && $this->HTMLFooterE === '') {
			return;
		}

		if ($this->setAutoBottomMargin == 'pad') {
			if (isset($htmlf['h']) && $htmlf['h']) {
				$h = $htmlf['h'];
			} // 5.7.3
			else {
				$h = 0;
			}
			$this->bMargin = $this->margin_footer + $h + $this->orig_bMargin;
			$this->PageBreakTrigger = $this->h - $this->bMargin;
		} elseif ($this->setAutoBottomMargin == 'stretch') {
			if (isset($htmlf['h']) && $htmlf['h']) {
				$h = $htmlf['h'];
			} // 5.7.3
			else {
				$h = 0;
			}
			$this->bMargin = max($this->orig_bMargin, $this->margin_footer + $h + $this->autoMarginPadding);
			$this->PageBreakTrigger = $this->h - $this->bMargin;
		}
	}

	function _endpage()
	{
		/* -- CSS-IMAGE-FLOAT -- */
		$this->printfloatbuffer();
		/* -- END CSS-IMAGE-FLOAT -- */

		if ($this->visibility != 'visible') {
			$this->SetVisibility('visible');
		}
		$this->EndLayer();
		// End of page contents
		$this->state = 1;
	}

	function _dounderline($x, $y, $txt, $OTLdata = false, $textvar = 0)
	{
		// Now print line exactly where $y secifies - called from Text() and Cell() - adjust  position there
		// WORD SPACING
		$w = ($this->GetStringWidth($txt, false, $OTLdata, $textvar) * Mpdf::SCALE) + ($this->charspacing * mb_strlen($txt, $this->mb_enc)) + ( $this->ws * mb_substr_count($txt, ' ', $this->mb_enc));
		// Draw a line
		return sprintf('%.3F %.3F m %.3F %.3F l S', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, ($x * Mpdf::SCALE) + $w, ($this->h - $y) * Mpdf::SCALE);
	}



	/* -- WATERMARK -- */

	// add a watermark
	function watermark($texte, $angle = 45, $fontsize = 96, $alpha = 0.2)
	{
		if ($this->PDFA || $this->PDFX) {
			throw new \Mpdf\MpdfException('PDFA and PDFX do not permit transparency, so mPDF does not allow Watermarks!');
		}

		if (!$this->watermark_font) {
			$this->watermark_font = $this->default_font;
		}

		$this->SetFont($this->watermark_font, "B", $fontsize, false); // Don't output
		$texte = $this->purify_utf8_text($texte);

		if ($this->text_input_as_HTML) {
			$texte = $this->all_entities_to_utf8($texte);
		}

		if ($this->usingCoreFont) {
			$texte = mb_convert_encoding($texte, $this->mb_enc, 'UTF-8');
		}

		// DIRECTIONALITY
		if (preg_match("/([" . $this->pregRTLchars . "])/u", $texte)) {
			$this->biDirectional = true;
		} // *OTL*

		$textvar = 0;
		$save_OTLtags = $this->OTLtags;
		$this->OTLtags = [];
		if ($this->useKerning) {
			if ($this->CurrentFont['haskernGPOS']) {
				$this->OTLtags['Plus'] .= ' kern';
			} else {
				$textvar = ($textvar | TextVars::FC_KERNING);
			}
		}

		/* -- OTL -- */
		// Use OTL OpenType Table Layout - GSUB & GPOS
		if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL']) {
			$texte = $this->otl->applyOTL($texte, $this->CurrentFont['useOTL']);
			$OTLdata = $this->otl->OTLdata;
		}
		/* -- END OTL -- */
		$this->OTLtags = $save_OTLtags;

		$this->magic_reverse_dir($texte, $this->directionality, $OTLdata);

		$this->SetAlpha($alpha);

		$color = $this->watermarkTextObject ? $this->watermarkTextObject->getColor() : 0;
		$this->SetTColor($this->colorConverter->convert($color, $this->PDFAXwarnings));

		$szfont = $fontsize;
		$loop = 0;
		$maxlen = (min($this->w, $this->h) ); // sets max length of text as 7/8 width/height of page

		while ($loop == 0) {
			$this->SetFont($this->watermark_font, "B", $szfont, false); // Don't output
			$offset = ((sin(deg2rad($angle))) * ($szfont / Mpdf::SCALE));

			$strlen = $this->GetStringWidth($texte, true, $OTLdata, $textvar);
			if ($strlen > $maxlen - $offset) {
				$szfont --;
			} else {
				$loop ++;
			}
		}

		$this->SetFont($this->watermark_font, "B", $szfont - 0.1, true, true); // Output The -0.1 is because SetFont above is not written to PDF

		// Repeating it will not output anything as mPDF thinks it is set
		$adj = ((cos(deg2rad($angle))) * ($strlen / 2));
		$opp = ((sin(deg2rad($angle))) * ($strlen / 2));

		$wx = ($this->w / 2) - $adj + $offset / 3;
		$wy = ($this->h / 2) + $opp;

		$this->Rotate($angle, $wx, $wy);
		$this->Text($wx, $wy, $texte, $OTLdata, $textvar);
		$this->Rotate(0);

		$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));

		$this->SetAlpha(1);
	}

	function watermarkImg($src, $alpha = 0.2)
	{
		if ($this->PDFA || $this->PDFX) {
			throw new \Mpdf\MpdfException('PDFA and PDFX do not permit transparency, so mPDF does not allow Watermarks!');
		}

		if ($this->watermarkImgBehind) {
			$this->watermarkImgAlpha = $this->SetAlpha($alpha, 'Normal', true);
		} else {
			$this->SetAlpha($alpha, $this->watermarkImgAlphaBlend);
		}

		$this->Image($src, 0, 0, 0, 0, '', '', true, true, true);

		if (!$this->watermarkImgBehind) {
			$this->SetAlpha(1);
		}
	}

	/* -- END WATERMARK -- */

	function Rotate($angle, $x = -1, $y = -1)
	{
		if ($x == -1) {
			$x = $this->x;
		}
		if ($y == -1) {
			$y = $this->y;
		}
		if ($this->angle != 0) {
			$this->writer->write('Q');
		}
		$this->angle = $angle;
		if ($angle != 0) {
			$angle*=M_PI / 180;
			$c = cos($angle);
			$s = sin($angle);
			$cx = $x * Mpdf::SCALE;
			$cy = ($this->h - $y) * Mpdf::SCALE;
			$this->writer->write(sprintf('q %.5F %.5F %.5F %.5F %.3F %.3F cm 1 0 0 1 %.3F %.3F cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
		}
	}

	function CircularText($x, $y, $r, $text, $align = 'top', $fontfamily = '', $fontsize = 0, $fontstyle = '', $kerning = 120, $fontwidth = 100, $divider = '')
	{
		if (empty($this->directWrite)) {
			$this->directWrite = new DirectWrite($this, $this->otl, $this->sizeConverter, $this->colorConverter);
		}

		$this->directWrite->CircularText($x, $y, $r, $text, $align, $fontfamily, $fontsize, $fontstyle, $kerning, $fontwidth, $divider);
	}

	// From Invoice
	function RoundedRect($x, $y, $w, $h, $r, $style = '')
	{
		$hp = $this->h;

		if ($style == 'F') {
			$op = 'f';
		} elseif ($style == 'FD' or $style == 'DF') {
			$op = 'B';
		} else {
			$op = 'S';
		}

		$MyArc = 4 / 3 * (sqrt(2) - 1);
		$this->writer->write(sprintf('%.3F %.3F m', ($x + $r) * Mpdf::SCALE, ($hp - $y) * Mpdf::SCALE));
		$xc = $x + $w - $r;
		$yc = $y + $r;
		$this->writer->write(sprintf('%.3F %.3F l', $xc * Mpdf::SCALE, ($hp - $y) * Mpdf::SCALE));

		$this->_Arc($xc + $r * $MyArc, $yc - $r, $xc + $r, $yc - $r * $MyArc, $xc + $r, $yc);
		$xc = $x + $w - $r;
		$yc = $y + $h - $r;
		$this->writer->write(sprintf('%.3F %.3F l', ($x + $w) * Mpdf::SCALE, ($hp - $yc) * Mpdf::SCALE));

		$this->_Arc($xc + $r, $yc + $r * $MyArc, $xc + $r * $MyArc, $yc + $r, $xc, $yc + $r);
		$xc = $x + $r;
		$yc = $y + $h - $r;
		$this->writer->write(sprintf('%.3F %.3F l', $xc * Mpdf::SCALE, ($hp - ($y + $h)) * Mpdf::SCALE));

		$this->_Arc($xc - $r * $MyArc, $yc + $r, $xc - $r, $yc + $r * $MyArc, $xc - $r, $yc);
		$xc = $x + $r;
		$yc = $y + $r;
		$this->writer->write(sprintf('%.3F %.3F l', ($x) * Mpdf::SCALE, ($hp - $yc) * Mpdf::SCALE));

		$this->_Arc($xc - $r, $yc - $r * $MyArc, $xc - $r * $MyArc, $yc - $r, $xc, $yc - $r);
		$this->writer->write($op);
	}

	function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
	{
		$h = $this->h;
		$this->writer->write(sprintf('%.3F %.3F %.3F %.3F %.3F %.3F c ', $x1 * Mpdf::SCALE, ($h - $y1) * Mpdf::SCALE, $x2 * Mpdf::SCALE, ($h - $y2) * Mpdf::SCALE, $x3 * Mpdf::SCALE, ($h - $y3) * Mpdf::SCALE));
	}

	// ====================================================



	/* -- DIRECTW -- */
	function Shaded_box($text, $font = '', $fontstyle = 'B', $szfont = '', $width = '70%', $style = 'DF', $radius = 2.5, $fill = '#FFFFFF', $color = '#000000', $pad = 2)
	{
		// F (shading - no line),S (line, no shading),DF (both)
		if (empty($this->directWrite)) {
			$this->directWrite = new DirectWrite($this, $this->otl, $this->sizeConverter, $this->colorConverter);
		}
		$this->directWrite->Shaded_box($text, $font, $fontstyle, $szfont, $width, $style, $radius, $fill, $color, $pad);
	}

	/* -- END DIRECTW -- */

	function UTF8StringToArray($str, $addSubset = true)
	{
		$out = [];
		$len = strlen($str);
		for ($i = 0; $i < $len; $i++) {
			$uni = -1;
			$h = ord($str[$i]);
			if ($h <= 0x7F) {
				$uni = $h;
			} elseif ($h >= 0xC2) {
				if (($h <= 0xDF) && ($i < $len - 1)) {
					$uni = ($h & 0x1F) << 6 | (ord($str[++$i]) & 0x3F);
				} elseif (($h <= 0xEF) && ($i < $len - 2)) {
					$uni = ($h & 0x0F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
				} elseif (($h <= 0xF4) && ($i < $len - 3)) {
					$uni = ($h & 0x0F) << 18 | (ord($str[++$i]) & 0x3F) << 12 | (ord($str[++$i]) & 0x3F) << 6 | (ord($str[++$i]) & 0x3F);
				}
			}
			if ($uni >= 0) {
				$out[] = $uni;
				if ($addSubset && isset($this->CurrentFont['subset'])) {
					$this->CurrentFont['subset'][$uni] = $uni;
				}
			}
		}
		return $out;
	}

	// Convert utf-8 string to <HHHHHH> for Font Subsets
	function UTF8toSubset($str)
	{
		$ret = '<';
		// $str = preg_replace('/'.preg_quote($this->aliasNbPg,'/').'/', chr(7), $str );	// mPDF 6 deleted
		// $str = preg_replace('/'.preg_quote($this->aliasNbPgGp,'/').'/', chr(8), $str );	// mPDF 6 deleted
		$unicode = $this->UTF8StringToArray($str);
		$orig_fid = $this->CurrentFont['subsetfontids'][0];
		$last_fid = $this->CurrentFont['subsetfontids'][0];
		foreach ($unicode as $c) {
			/* 	// mPDF 6 deleted
			  if ($c == 7 || $c == 8) {
			  if ($orig_fid != $last_fid) {
			  $ret .= '> Tj /F'.$orig_fid.' '.$this->FontSizePt.' Tf <';
			  $last_fid = $orig_fid;
			  }
			  if ($c == 7) { $ret .= $this->aliasNbPgHex; }
			  else { $ret .= $this->aliasNbPgGpHex; }
			  continue;
			  }
			 */
			if (!$this->_charDefined($this->CurrentFont['cw'], $c)) {
				$c = 0;
			} // mPDF 6
			for ($i = 0; $i < 99; $i++) {
				// return c as decimal char
				$init = array_search($c, $this->CurrentFont['subsets'][$i]);
				if ($init !== false) {
					if ($this->CurrentFont['subsetfontids'][$i] != $last_fid) {
						$ret .= '> Tj /F' . $this->CurrentFont['subsetfontids'][$i] . ' ' . $this->FontSizePt . ' Tf <';
						$last_fid = $this->CurrentFont['subsetfontids'][$i];
					}
					$ret .= sprintf("%02s", strtoupper(dechex($init)));
					break;
				} // TrueType embedded SUBSETS
				elseif (count($this->CurrentFont['subsets'][$i]) < 255) {
					$n = count($this->CurrentFont['subsets'][$i]);
					$this->CurrentFont['subsets'][$i][$n] = $c;
					if ($this->CurrentFont['subsetfontids'][$i] != $last_fid) {
						$ret .= '> Tj /F' . $this->CurrentFont['subsetfontids'][$i] . ' ' . $this->FontSizePt . ' Tf <';
						$last_fid = $this->CurrentFont['subsetfontids'][$i];
					}
					$ret .= sprintf("%02s", strtoupper(dechex($n)));
					break;
				} elseif (!isset($this->CurrentFont['subsets'][($i + 1)])) {
					// TrueType embedded SUBSETS
					$this->CurrentFont['subsets'][($i + 1)] = [0 => 0];
					$new_fid = count($this->fonts) + $this->extraFontSubsets + 1;
					$this->CurrentFont['subsetfontids'][($i + 1)] = $new_fid;
					$this->extraFontSubsets++;
				}
			}
		}
		$ret .= '>';
		if ($last_fid != $orig_fid) {
			$ret .= ' Tj /F' . $orig_fid . ' ' . $this->FontSizePt . ' Tf <> ';
		}
		return $ret;
	}

	/* -- CJK-FONTS -- */

	// from class PDF_Chinese CJK EXTENSIONS
	function AddCIDFont($family, $style, $name, &$cw, $CMap, $registry, $desc)
	{
		$fontkey = strtolower($family) . strtoupper($style);
		if (isset($this->fonts[$fontkey])) {
			throw new \Mpdf\MpdfException("Font already added: $family $style");
		}
		$i = count($this->fonts) + $this->extraFontSubsets + 1;
		$name = str_replace(' ', '', $name);
		if ($family == 'sjis') {
			$up = -120;
		} else {
			$up = -130;
		}
		// ? 'up' and 'ut' do not seem to be referenced anywhere
		$this->fonts[$fontkey] = ['i' => $i, 'type' => 'Type0', 'name' => $name, 'up' => $up, 'ut' => 40, 'cw' => $cw, 'CMap' => $CMap, 'registry' => $registry, 'MissingWidth' => 1000, 'desc' => $desc];
	}

	function AddCJKFont($family)
	{

		if ($this->PDFA || $this->PDFX) {
			throw new \Mpdf\MpdfException("Adobe CJK fonts cannot be embedded in mPDF (required for PDFA1-b and PDFX/1-a).");
		}
		if ($family == 'big5') {
			$this->AddBig5Font();
		} elseif ($family == 'gb') {
			$this->AddGBFont();
		} elseif ($family == 'sjis') {
			$this->AddSJISFont();
		} elseif ($family == 'uhc') {
			$this->AddUHCFont();
		}
	}

	function AddBig5Font()
	{
		// Add Big5 font with proportional Latin
		$family = 'big5';
		$name = 'MSungStd-Light-Acro';
		$cw = $this->Big5_widths;
		$CMap = 'UniCNS-UTF16-H';
		$registry = ['ordering' => 'CNS1', 'supplement' => 4];
		$desc = [
			'Ascent' => 880,
			'Descent' => -120,
			'CapHeight' => 880,
			'Flags' => 6,
			'FontBBox' => '[-160 -249 1015 1071]',
			'ItalicAngle' => 0,
			'StemV' => 93,
		];
		$this->AddCIDFont($family, '', $name, $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'B', $name . ',Bold', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'I', $name . ',Italic', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'BI', $name . ',BoldItalic', $cw, $CMap, $registry, $desc);
	}

	function AddGBFont()
	{
		// Add GB font with proportional Latin
		$family = 'gb';
		$name = 'STSongStd-Light-Acro';
		$cw = $this->GB_widths;
		$CMap = 'UniGB-UTF16-H';
		$registry = ['ordering' => 'GB1', 'supplement' => 4];
		$desc = [
			'Ascent' => 880,
			'Descent' => -120,
			'CapHeight' => 737,
			'Flags' => 6,
			'FontBBox' => '[-25 -254 1000 880]',
			'ItalicAngle' => 0,
			'StemV' => 58,
			'Style' => '<< /Panose <000000000400000000000000> >>',
		];
		$this->AddCIDFont($family, '', $name, $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'B', $name . ',Bold', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'I', $name . ',Italic', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'BI', $name . ',BoldItalic', $cw, $CMap, $registry, $desc);
	}

	function AddSJISFont()
	{
		// Add SJIS font with proportional Latin
		$family = 'sjis';
		$name = 'KozMinPro-Regular-Acro';
		$cw = $this->SJIS_widths;
		$CMap = 'UniJIS-UTF16-H';
		$registry = ['ordering' => 'Japan1', 'supplement' => 5];
		$desc = [
			'Ascent' => 880,
			'Descent' => -120,
			'CapHeight' => 740,
			'Flags' => 6,
			'FontBBox' => '[-195 -272 1110 1075]',
			'ItalicAngle' => 0,
			'StemV' => 86,
			'XHeight' => 502,
		];
		$this->AddCIDFont($family, '', $name, $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'B', $name . ',Bold', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'I', $name . ',Italic', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'BI', $name . ',BoldItalic', $cw, $CMap, $registry, $desc);
	}

	function AddUHCFont()
	{
		// Add UHC font with proportional Latin
		$family = 'uhc';
		$name = 'HYSMyeongJoStd-Medium-Acro';
		$cw = $this->UHC_widths;
		$CMap = 'UniKS-UTF16-H';
		$registry = ['ordering' => 'Korea1', 'supplement' => 2];
		$desc = [
			'Ascent' => 880,
			'Descent' => -120,
			'CapHeight' => 720,
			'Flags' => 6,
			'FontBBox' => '[-28 -148 1001 880]',
			'ItalicAngle' => 0,
			'StemV' => 60,
			'Style' => '<< /Panose <000000000600000000000000> >>',
		];
		$this->AddCIDFont($family, '', $name, $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'B', $name . ',Bold', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'I', $name . ',Italic', $cw, $CMap, $registry, $desc);
		$this->AddCIDFont($family, 'BI', $name . ',BoldItalic', $cw, $CMap, $registry, $desc);
	}

	/* -- END CJK-FONTS -- */

	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////////////////////////

	function SetDefaultFont($font)
	{
		// Disallow embedded fonts to be used as defaults in PDFA
		if ($this->PDFA || $this->PDFX) {
			if (strtolower($font) == 'ctimes') {
				$font = 'serif';
			}
			if (strtolower($font) == 'ccourier') {
				$font = 'monospace';
			}
			if (strtolower($font) == 'chelvetica') {
				$font = 'sans-serif';
			}
		}
		$font = $this->SetFont($font); // returns substituted font if necessary
		$this->default_font = $font;
		$this->original_default_font = $font;
		if (!$this->watermark_font) {
			$this->watermark_font = $font;
		} // *WATERMARK*
		$this->defaultCSS['BODY']['FONT-FAMILY'] = $font;
		$this->cssManager->CSS['BODY']['FONT-FAMILY'] = $font;
	}

	function SetDefaultFontSize($fontsize)
	{
		$this->default_font_size = $fontsize;
		$this->original_default_font_size = $fontsize;
		$this->SetFontSize($fontsize);
		$this->defaultCSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
		$this->cssManager->CSS['BODY']['FONT-SIZE'] = $fontsize . 'pt';
	}

	function SetDefaultBodyCSS($prop, $val)
	{
		if ($prop) {
			$this->defaultCSS['BODY'][strtoupper($prop)] = $val;
			$this->cssManager->CSS['BODY'][strtoupper($prop)] = $val;
		}
	}

	function SetDirectionality($dir = 'ltr')
	{
		/* -- OTL -- */
		if (strtolower($dir) == 'rtl') {
			if ($this->directionality != 'rtl') {
				// Swop L/R Margins so page 1 RTL is an 'even' page
				$tmp = $this->DeflMargin;
				$this->DeflMargin = $this->DefrMargin;
				$this->DefrMargin = $tmp;
				$this->orig_lMargin = $this->DeflMargin;
				$this->orig_rMargin = $this->DefrMargin;

				$this->SetMargins($this->DeflMargin, $this->DefrMargin, $this->tMargin);
			}
			$this->directionality = 'rtl';
			$this->defaultAlign = 'R';
			$this->defaultTableAlign = 'R';
		} else {
			/* -- END OTL -- */
			$this->directionality = 'ltr';
			$this->defaultAlign = 'L';
			$this->defaultTableAlign = 'L';
		} // *OTL*
		$this->cssManager->CSS['BODY']['DIRECTION'] = $this->directionality;
	}

	// Return either a number (factor) - based on current set fontsize (if % or em) - or exact lineheight (with 'mm' after it)
	function fixLineheight($v)
	{
		$lh = false;
		if (preg_match('/^[0-9\.,]*$/', $v) && $v >= 0) {
			return ($v + 0);
		} elseif (strtoupper($v) == 'NORMAL' || $v == 'N') {
			return 'N';  // mPDF 6
		} else {
			$tlh = $this->sizeConverter->convert($v, $this->FontSize, $this->FontSize, true);
			if ($tlh) {
				return ($tlh . 'mm');
			}
		}
		return $this->normalLineheight;
	}

	function _getNormalLineheight($desc = false)
	{
		if (!$desc) {
			$desc = $this->CurrentFont['desc'];
		}
		if (!isset($desc['Leading'])) {
			$desc['Leading'] = 0;
		}
		if ($this->useFixedNormalLineHeight) {
			$lh = $this->normalLineheight;
		} elseif (isset($desc['Ascent']) && $desc['Ascent']) {
			$lh = ($this->adjustFontDescLineheight * ($desc['Ascent'] - $desc['Descent'] + $desc['Leading']) / 1000);
		} else {
			$lh = $this->normalLineheight;
		}
		return $lh;
	}

	// Set a (fixed) lineheight to an actual value - either to named fontsize(pts) or default
	function SetLineHeight($FontPt = '', $lh = '')
	{
		if (!$FontPt) {
			$FontPt = $this->FontSizePt;
		}
		$fs = $FontPt / Mpdf::SCALE;
		$this->lineheight = $this->_computeLineheight($lh, $fs);
	}

	function _computeLineheight($lh, $fs = '')
	{
		if ($this->shrin_k > 1) {
			$k = $this->shrin_k;
		} else {
			$k = 1;
		}
		if (!$fs) {
			$fs = $this->FontSize;
		}
		if ($lh == 'N') {
			$lh = $this->_getNormalLineheight();
		}
		if (preg_match('/mm/', $lh)) {
			return (((float) $lh) / $k); // convert to number
		} elseif ($lh > 0) {
			return ($fs * $lh);
		}
		return ($fs * $this->normalLineheight);
	}

	function _setLineYpos(&$fontsize, &$fontdesc, &$CSSlineheight, $blockYpos = false)
	{
		$ypos['glyphYorigin'] = 0;
		$ypos['baseline-shift'] = 0;
		$linegap = 0;
		$leading = 0;

		if (isset($fontdesc['Ascent']) && $fontdesc['Ascent'] && !$this->useFixedTextBaseline) {
			// Fontsize uses font metrics - this method seems to produce results compatible with browsers (except IE9)
			$ypos['boxtop'] = $fontdesc['Ascent'] / 1000 * $fontsize;
			$ypos['boxbottom'] = $fontdesc['Descent'] / 1000 * $fontsize;
			if (isset($fontdesc['Leading'])) {
				$linegap = $fontdesc['Leading'] / 1000 * $fontsize;
			}
		} // Default if not set - uses baselineC
		else {
			$ypos['boxtop'] = (0.5 + $this->baselineC) * $fontsize;
			$ypos['boxbottom'] = -(0.5 - $this->baselineC) * $fontsize;
		}
		$fontheight = $ypos['boxtop'] - $ypos['boxbottom'];

		if ($this->shrin_k > 1) {
			$shrin_k = $this->shrin_k;
		} else {
			$shrin_k = 1;
		}

		$leading = 0;
		if ($CSSlineheight == 'N') {
			$lh = $this->_getNormalLineheight($fontdesc);
			$lineheight = ($fontsize * $lh);
			$leading += $linegap; // specified in hhea or sTypo in OpenType tables
		} elseif (preg_match('/mm/', $CSSlineheight)) {
			$lineheight = (((float) $CSSlineheight) / $shrin_k); // convert to number
		} // ??? If lineheight is a factor e.g. 1.3  ?? use factor x 1em or ? use 'normal' lineheight * factor
		// Could depend on value for $text_height - a draft CSS value as set above for now
		elseif ($CSSlineheight > 0) {
			$lineheight = ($fontsize * $CSSlineheight);
		} else {
			$lineheight = ($fontsize * $this->normalLineheight);
		}

		// In general, calculate the "leading" - the difference between the fontheight and the lineheight
		// and add half to the top and half to the bottom. BUT
		// If an inline element has a font-size less than the block element, and the line-height is set as an em or % value
		// it will add too much leading below the font and expand the height of the line - so just use the block element exttop/extbottom:
		if (preg_match('/mm/', $CSSlineheight)
				&& ($blockYpos && $ypos['boxtop'] < $blockYpos['boxtop'])
				&& ($blockYpos && $ypos['boxbottom'] > $blockYpos['boxbottom'])) {

			$ypos['exttop'] = $blockYpos['exttop'];
			$ypos['extbottom'] = $blockYpos['extbottom'];

		} else {

			$leading += ($lineheight - $fontheight);

			$ypos['exttop'] = $ypos['boxtop'] + $leading / 2;
			$ypos['extbottom'] = $ypos['boxbottom'] - $leading / 2;
		}


		// TEMP ONLY FOR DEBUGGING *********************************
		// $ypos['lineheight'] = $lineheight;
		// $ypos['fontheight'] = $fontheight;
		// $ypos['leading'] = $leading;

		return $ypos;
	}

	/* Called from WriteFlowingBlock() and finishFlowingBlock()
	  Determines the line hieght and glyph/writing position
	  for each element in the line to be written */

	function _setInlineBlockHeights(&$lineBox, &$stackHeight, &$content, &$font, $is_table)
	{
		if ($this->shrin_k > 1) {
			$shrin_k = $this->shrin_k;
		} else {
			$shrin_k = 1;
		}

		$ypos = [];
		$bordypos = [];
		$bgypos = [];

		if ($is_table) {
			// FOR TABLE
			$fontsize = $this->FontSize;
			$fontkey = $this->FontFamily . $this->FontStyle;
			$fontdesc = $this->fonts[$fontkey]['desc'];
			$CSSlineheight = $this->cellLineHeight;
			$line_stacking_strategy = $this->cellLineStackingStrategy; // inline-line-height [default] | block-line-height | max-height | grid-height
			$line_stacking_shift = $this->cellLineStackingShift;  // consider-shifts [default] | disregard-shifts
		} else {
			// FOR BLOCK FONT
			$fontsize = $this->blk[$this->blklvl]['InlineProperties']['size'];
			$fontkey = $this->blk[$this->blklvl]['InlineProperties']['family'] . $this->blk[$this->blklvl]['InlineProperties']['style'];
			$fontdesc = $this->fonts[$fontkey]['desc'];
			$CSSlineheight = $this->blk[$this->blklvl]['line_height'];
			// inline-line-height | block-line-height | max-height | grid-height
			$line_stacking_strategy = (isset($this->blk[$this->blklvl]['line_stacking_strategy']) ? $this->blk[$this->blklvl]['line_stacking_strategy'] : 'inline-line-height');
			// consider-shifts | disregard-shifts
			$line_stacking_shift = (isset($this->blk[$this->blklvl]['line_stacking_shift']) ? $this->blk[$this->blklvl]['line_stacking_shift'] : 'consider-shifts');
		}
		$boxLineHeight = $this->_computeLineheight($CSSlineheight, $fontsize);


		// First, set a "strut" using block font at index $lineBox[-1]
		$ypos[-1] = $this->_setLineYpos($fontsize, $fontdesc, $CSSlineheight);

		// for the block element - always taking the block EXTENDED progression including leading - which may be negative
		if ($line_stacking_strategy == 'block-line-height') {
			$topy = $ypos[-1]['exttop'];
			$bottomy = $ypos[-1]['extbottom'];
		} else {
			$topy = 0;
			$bottomy = 0;
		}

		// Get text-middle for aligning images/objects
		$midpoint = $ypos[-1]['boxtop'] - (($ypos[-1]['boxtop'] - $ypos[-1]['boxbottom']) / 2);

		// for images / inline objects / replaced elements
		$mta = 0; // Maximum top-aligned
		$mba = 0; // Maximum bottom-aligned
		foreach ($content as $k => $chunk) {
			if (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]['type'] == 'listmarker') {
				$ypos[$k] = $ypos[-1];
				// UPDATE Maximums
				if ($line_stacking_strategy == 'block-line-height' || $line_stacking_strategy == 'grid-height' || $line_stacking_strategy == 'max-height') { // don't include extended block progression of all inline elements
					if ($ypos[$k]['boxtop'] > $topy) {
						$topy = $ypos[$k]['boxtop'];
					}
					if ($ypos[$k]['boxbottom'] < $bottomy) {
						$bottomy = $ypos[$k]['boxbottom'];
					}
				} else {
					if ($ypos[$k]['exttop'] > $topy) {
						$topy = $ypos[$k]['exttop'];
					}
					if ($ypos[$k]['extbottom'] < $bottomy) {
						$bottomy = $ypos[$k]['extbottom'];
					}
				}
			} elseif (isset($this->objectbuffer[$k]) && $this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
				$fontsize = $font[$k]['size'];
				$fontdesc = $font[$k]['curr']['desc'];
				$lh = 1;
				$ypos[$k] = $this->_setLineYpos($fontsize, $fontdesc, $lh, $ypos[-1]); // Lineheight=1 fixed
			} elseif (isset($this->objectbuffer[$k])) {
				$oh = $this->objectbuffer[$k]['OUTER-HEIGHT'];
				$va = $this->objectbuffer[$k]['vertical-align'];

				if ($va == 'BS') { //  (BASELINE default)
					if ($oh > $topy) {
						$topy = $oh;
					}
				} elseif ($va == 'M') {
					if (($midpoint + $oh / 2) > $topy) {
						$topy = $midpoint + $oh / 2;
					}
					if (($midpoint - $oh / 2) < $bottomy) {
						$bottomy = $midpoint - $oh / 2;
					}
				} elseif ($va == 'TT') {
					if (($ypos[-1]['boxtop'] - $oh) < $bottomy) {
						$bottomy = $ypos[-1]['boxtop'] - $oh;
						$topy = max($topy, $ypos[-1]['boxtop']);
					}
				} elseif ($va == 'TB') {
					if (($ypos[-1]['boxbottom'] + $oh) > $topy) {
						$topy = $ypos[-1]['boxbottom'] + $oh;
						$bottomy = min($bottomy, $ypos[-1]['boxbottom']);
					}
				} elseif ($va == 'T') {
					if ($oh > $mta) {
						$mta = $oh;
					}
				} elseif ($va == 'B') {
					if ($oh > $mba) {
						$mba = $oh;
					}
				}
			} elseif ($content[$k] || $content[$k] === '0') {
				// FOR FLOWING BLOCK
				$fontsize = $font[$k]['size'];
				$fontdesc = $font[$k]['curr']['desc'];
				// In future could set CSS line-height from inline elements; for now, use block level:
				$ypos[$k] = $this->_setLineYpos($fontsize, $fontdesc, $CSSlineheight, $ypos[-1]);

				if (isset($font[$k]['textparam']['text-baseline']) && $font[$k]['textparam']['text-baseline'] != 0) {
					$ypos[$k]['baseline-shift'] = $font[$k]['textparam']['text-baseline'];
				}

				// DO ALIGNMENT FOR BASELINES *******************
				// Until most fonts have OpenType BASE tables, this won't work
				// $ypos[$k] compared to $ypos[-1] or $ypos[$k-1] using $dominant_baseline and $baseline_table
				// UPDATE Maximums
				if ($line_stacking_strategy == 'block-line-height' || $line_stacking_strategy == 'grid-height' || $line_stacking_strategy == 'max-height') { // don't include extended block progression of all inline elements
					if ($line_stacking_shift == 'disregard-shifts') {
						if ($ypos[$k]['boxtop'] > $topy) {
							$topy = $ypos[$k]['boxtop'];
						}
						if ($ypos[$k]['boxbottom'] < $bottomy) {
							$bottomy = $ypos[$k]['boxbottom'];
						}
					} else {
						if (($ypos[$k]['boxtop'] + $ypos[$k]['baseline-shift']) > $topy) {
							$topy = $ypos[$k]['boxtop'] + $ypos[$k]['baseline-shift'];
						}
						if (($ypos[$k]['boxbottom'] + $ypos[$k]['baseline-shift']) < $bottomy) {
							$bottomy = $ypos[$k]['boxbottom'] + $ypos[$k]['baseline-shift'];
						}
					}
				} else {
					if ($line_stacking_shift == 'disregard-shifts') {
						if ($ypos[$k]['exttop'] > $topy) {
							$topy = $ypos[$k]['exttop'];
						}
						if ($ypos[$k]['extbottom'] < $bottomy) {
							$bottomy = $ypos[$k]['extbottom'];
						}
					} else {
						if (($ypos[$k]['exttop'] + $ypos[$k]['baseline-shift']) > $topy) {
							$topy = $ypos[$k]['exttop'] + $ypos[$k]['baseline-shift'];
						}
						if (($ypos[$k]['extbottom'] + $ypos[$k]['baseline-shift']) < $bottomy) {
							$bottomy = $ypos[$k]['extbottom'] + $ypos[$k]['baseline-shift'];
						}
					}
				}

				// If BORDER set on inline element
				if (isset($font[$k]['bord']) && $font[$k]['bord']) {
					$bordfontsize = $font[$k]['textparam']['bord-decoration']['fontsize'] / $shrin_k;
					$bordfontkey = $font[$k]['textparam']['bord-decoration']['fontkey'];
					if ($bordfontkey != $fontkey || $bordfontsize != $fontsize || isset($font[$k]['textparam']['bord-decoration']['baseline'])) {
						$bordfontdesc = $this->fonts[$bordfontkey]['desc'];
						$bordypos[$k] = $this->_setLineYpos($bordfontsize, $bordfontdesc, $CSSlineheight, $ypos[-1]);
						if (isset($font[$k]['textparam']['bord-decoration']['baseline']) && $font[$k]['textparam']['bord-decoration']['baseline'] != 0) {
							$bordypos[$k]['baseline-shift'] = $font[$k]['textparam']['bord-decoration']['baseline'] / $shrin_k;
						}
					}
				}
				// If BACKGROUND set on inline element
				if (isset($font[$k]['spanbgcolor']) && $font[$k]['spanbgcolor']) {
					$bgfontsize = $font[$k]['textparam']['bg-decoration']['fontsize'] / $shrin_k;
					$bgfontkey = $font[$k]['textparam']['bg-decoration']['fontkey'];
					if ($bgfontkey != $fontkey || $bgfontsize != $fontsize || isset($font[$k]['textparam']['bg-decoration']['baseline'])) {
						$bgfontdesc = $this->fonts[$bgfontkey]['desc'];
						$bgypos[$k] = $this->_setLineYpos($bgfontsize, $bgfontdesc, $CSSlineheight, $ypos[-1]);
						if (isset($font[$k]['textparam']['bg-decoration']['baseline']) && $font[$k]['textparam']['bg-decoration']['baseline'] != 0) {
							$bgypos[$k]['baseline-shift'] = $font[$k]['textparam']['bg-decoration']['baseline'] / $shrin_k;
						}
					}
				}
			}
		}


		// TOP or BOTTOM aligned images
		if ($mta > ($topy - $bottomy)) {
			if (($topy - $mta) < $bottomy) {
				$bottomy = $topy - $mta;
			}
		}
		if ($mba > ($topy - $bottomy)) {
			if (($bottomy + $mba) > $topy) {
				$topy = $bottomy + $mba;
			}
		}

		if ($line_stacking_strategy == 'block-line-height') { // fixed height set by block element (whether present or not)
			$topy = $ypos[-1]['exttop'];
			$bottomy = $ypos[-1]['extbottom'];
		}

		$inclusiveHeight = $topy - $bottomy;

		// SET $stackHeight taking note of line_stacking_strategy
		// NB inclusive height already takes account of need to consider block progression height (excludes leading set by lineheight)
		// or extended block progression height (includes leading set by lineheight)
		if ($line_stacking_strategy == 'block-line-height') { // fixed = extended block progression height of block element
			$stackHeight = $boxLineHeight;
		} elseif ($line_stacking_strategy == 'max-height') { // smallest height which includes extended block progression height of block element
			// and block progression heights of inline elements (NOT extended)
			$stackHeight = $inclusiveHeight;
		} elseif ($line_stacking_strategy == 'grid-height') { // smallest multiple of block element lineheight to include
			// block progression heights of inline elements (NOT extended)
			$stackHeight = $boxLineHeight;
			while ($stackHeight < $inclusiveHeight) {
				$stackHeight += $boxLineHeight;
			}
		} else { // 'inline-line-height' = default		// smallest height which includes extended block progression height of block element
			// AND extended block progression heights of inline elements
			$stackHeight = $inclusiveHeight;
		}

		$diff = $stackHeight - $inclusiveHeight;
		$topy += $diff / 2;
		$bottomy -= $diff / 2;

		// ADJUST $ypos => lineBox using $stackHeight; lineBox are all offsets from the top of stackHeight in mm
		// and SET IMAGE OFFSETS
		$lineBox[-1]['boxtop'] = $topy - $ypos[-1]['boxtop'];
		$lineBox[-1]['boxbottom'] = $topy - $ypos[-1]['boxbottom'];
		// $lineBox[-1]['exttop'] = $topy - $ypos[-1]['exttop'];
		// $lineBox[-1]['extbottom'] = $topy - $ypos[-1]['extbottom'];
		$lineBox[-1]['glyphYorigin'] = $topy - $ypos[-1]['glyphYorigin'];
		$lineBox[-1]['baseline-shift'] = $ypos[-1]['baseline-shift'];

		$midpoint = $lineBox[-1]['boxbottom'] - (($lineBox[-1]['boxbottom'] - $lineBox[-1]['boxtop']) / 2);

		foreach ($content as $k => $chunk) {
			if (isset($this->objectbuffer[$k])) {
				$oh = $this->objectbuffer[$k]['OUTER-HEIGHT'];
				// LIST MARKERS
				if ($this->objectbuffer[$k]['type'] == 'listmarker') {
					$oh = $fontsize;
				} elseif ($this->objectbuffer[$k]['type'] == 'dottab') { // mPDF 6 DOTTAB
					$oh = $font[$k]['size']; // == $this->objectbuffer[$k]['fontsize']/Mpdf::SCALE;
					$lineBox[$k]['boxtop'] = $topy - $ypos[$k]['boxtop'];
					$lineBox[$k]['boxbottom'] = $topy - $ypos[$k]['boxbottom'];
					$lineBox[$k]['glyphYorigin'] = $topy - $ypos[$k]['glyphYorigin'];
					$lineBox[$k]['baseline-shift'] = 0;
					// continue;
				}
				$va = $this->objectbuffer[$k]['vertical-align']; // = $objattr['vertical-align'] = set as M,T,B,S

				if ($va == 'BS') { //  (BASELINE default)
					$lineBox[$k]['top'] = $lineBox[-1]['glyphYorigin'] - $oh;
				} elseif ($va == 'M') {
					$lineBox[$k]['top'] = $midpoint - $oh / 2;
				} elseif ($va == 'TT') {
					$lineBox[$k]['top'] = $lineBox[-1]['boxtop'];
				} elseif ($va == 'TB') {
					$lineBox[$k]['top'] = $lineBox[-1]['boxbottom'] - $oh;
				} elseif ($va == 'T') {
					$lineBox[$k]['top'] = 0;
				} elseif ($va == 'B') {
					$lineBox[$k]['top'] = $stackHeight - $oh;
				}
			} elseif ($content[$k] || $content[$k] === '0') {
				$lineBox[$k]['boxtop'] = $topy - $ypos[$k]['boxtop'];
				$lineBox[$k]['boxbottom'] = $topy - $ypos[$k]['boxbottom'];
				// $lineBox[$k]['exttop'] = $topy - $ypos[$k]['exttop'];
				// $lineBox[$k]['extbottom'] = $topy - $ypos[$k]['extbottom'];
				$lineBox[$k]['glyphYorigin'] = $topy - $ypos[$k]['glyphYorigin'];
				$lineBox[$k]['baseline-shift'] = $ypos[$k]['baseline-shift'];
				if (isset($bordypos[$k]['boxtop'])) {
					$lineBox[$k]['border-boxtop'] = $topy - $bordypos[$k]['boxtop'];
					$lineBox[$k]['border-boxbottom'] = $topy - $bordypos[$k]['boxbottom'];
					$lineBox[$k]['border-baseline-shift'] = $bordypos[$k]['baseline-shift'];
				}
				if (isset($bgypos[$k]['boxtop'])) {
					$lineBox[$k]['background-boxtop'] = $topy - $bgypos[$k]['boxtop'];
					$lineBox[$k]['background-boxbottom'] = $topy - $bgypos[$k]['boxbottom'];
					$lineBox[$k]['background-baseline-shift'] = $bgypos[$k]['baseline-shift'];
				}
			}
		}
	}

	function SetBasePath($str = '')
	{
		if (isset($_SERVER['HTTP_HOST'])) {
			$host = $_SERVER['HTTP_HOST'];
		} elseif (isset($_SERVER['SERVER_NAME'])) {
			$host = $_SERVER['SERVER_NAME'];
		} else {
			$host = '';
		}

		if (!$str) {

			if (isset($_SERVER['SCRIPT_NAME'])) {
				$currentPath = dirname($_SERVER['SCRIPT_NAME']);
			} else {
				$currentPath = dirname($_SERVER['PHP_SELF']);
			}

			$currentPath = str_replace("\\", "/", $currentPath);

			if ($currentPath == '/') {
				$currentPath = '';
			}

			if ($host) {  // mPDF 6
				if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] && $_SERVER['HTTPS'] !== 'off') {
					$currpath = 'https://' . $host . $currentPath . '/';
				} else {
					$currpath = 'http://' . $host . $currentPath . '/';
				}
			} else {
				$currpath = '';
			}

			$this->basepath = $currpath;
			$this->basepathIsLocal = true;

			return;
		}

		$str = preg_replace('/\?.*/', '', $str);

		if (!preg_match('/(http|https|ftp):\/\/.*\//i', $str)) {
			$str .= '/';
		}

		$str .= 'xxx'; // in case $str ends in / e.g. http://www.bbc.co.uk/

		$this->basepath = dirname($str) . "/"; // returns e.g. e.g. http://www.google.com/dir1/dir2/dir3/
		$this->basepath = str_replace("\\", "/", $this->basepath); // If on Windows

		$tr = parse_url($this->basepath);

		$this->basepathIsLocal = (isset($tr['host']) && ($tr['host'] == $host));
	}

	public function GetFullPath(&$path, $basepath = '')
	{
		// @todo make return, remove reference

		// When parsing CSS need to pass temporary basepath - so links are relative to current stylesheet
		if (!$basepath) {
			$basepath = $this->basepath;
		}

		// Fix path value
		$path = str_replace("\\", '/', $path); // If on Windows

		// mPDF 5.7.2
		if (strpos($path, '//') === 0) {
			$scheme = parse_url($basepath, PHP_URL_SCHEME);
			$scheme = $scheme ?: 'http';
			$path = $scheme . ':' . $path;
		}

		$path = preg_replace('|^./|', '', $path); // Inadvertently corrects "./path/etc" and "//www.domain.com/etc"

		if (strpos($path, '#') === 0) {
			return;
		}

		// Skip schemes not supported by installed stream wrappers
		$wrappers = stream_get_wrappers();
		$pattern = sprintf('@^(?!%s)[a-z0-9\.\-+]+:.*@i', implode('|', $wrappers));
		if (preg_match($pattern, $path)) {
			return;
		}

		if (strpos($path, '../') === 0) { // It is a relative link

			$backtrackamount = substr_count($path, '../');
			$maxbacktrack = substr_count($basepath, '/') - 3;
			$filepath = str_replace('../', '', $path);
			$path = $basepath;

			// If it is an invalid relative link, then make it go to directory root
			if ($backtrackamount > $maxbacktrack) {
				$backtrackamount = $maxbacktrack;
			}

			// Backtrack some directories
			for ($i = 0; $i < $backtrackamount + 1; $i++) {
				$path = substr($path, 0, strrpos($path, "/"));
			}

			$path .= '/' . $filepath; // Make it an absolute path

			return;

		}

		if ((strpos($path, ":/") === false || strpos($path, ":/") > 10) && !@is_file($path)) { // It is a local link. Ignore potential file errors

			if (strpos($path, '/') === 0) {

				$tr = parse_url($basepath);

				// mPDF 5.7.2
				$root = '';
				if (!empty($tr['scheme'])) {
					$root .= $tr['scheme'] . '://';
				}

				$root .= isset($tr['host']) ? $tr['host'] : '';
				$root .= ((isset($tr['port']) && $tr['port']) ? (':' . $tr['port']) : ''); // mPDF 5.7.3

				$path = $root . $path;

				return;

			}

			$path = $basepath . $path;
		}

		// Do nothing if it is an Absolute Link
	}

	function docPageNum($num = 0, $extras = false)
	{
		if ($num < 1) {
			$num = $this->page;
		}

		$type = $this->defaultPageNumStyle; // set default Page Number Style
		$ppgno = $num;
		$suppress = 0;
		$offset = 0;
		$lastreset = 0;

		foreach ($this->PageNumSubstitutions as $psarr) {

			if ($num >= $psarr['from']) {

				if ($psarr['reset']) {
					if ($psarr['reset'] > 1) {
						$offset = $psarr['reset'] - 1;
					}
					$ppgno = $num - $psarr['from'] + 1 + $offset;
					$lastreset = $psarr['from'];
				}

				if ($psarr['type']) {
					$type = $psarr['type'];
				}

				if (strtoupper($psarr['suppress']) == 'ON' || $psarr['suppress'] == 1) {
					$suppress = 1;
				} elseif (strtoupper($psarr['suppress']) == 'OFF') {
					$suppress = 0;
				}
			}
		}

		if ($suppress) {
			return '';
		}

		$ppgno = $this->_getStyledNumber($ppgno, $type);

		if ($extras) {
			$ppgno = $this->pagenumPrefix . $ppgno . $this->pagenumSuffix;
		}

		return $ppgno;
	}

	function docPageNumTotal($num = 0, $extras = false)
	{
		if ($num < 1) {
			$num = $this->page;
		}

		$type = $this->defaultPageNumStyle; // set default Page Number Style
		$ppgstart = 1;
		$ppgend = count($this->pages) + 1;
		$suppress = 0;
		$offset = 0;

		foreach ($this->PageNumSubstitutions as $psarr) {
			if ($num >= $psarr['from']) {
				if ($psarr['reset']) {
					if ($psarr['reset'] > 1) {
						$offset = $psarr['reset'] - 1;
					}
					$ppgstart = $psarr['from'] + $offset;
					$ppgend = count($this->pages) + 1 + $offset;
				}
				if ($psarr['type']) {
					$type = $psarr['type'];
				}
				if (strtoupper($psarr['suppress']) == 'ON' || $psarr['suppress'] == 1) {
					$suppress = 1;
				} elseif (strtoupper($psarr['suppress']) == 'OFF') {
					$suppress = 0;
				}
			}
			if ($num < $psarr['from']) {
				if ($psarr['reset']) {
					$ppgend = $psarr['from'] + $offset;
					break;
				}
			}
		}

		if ($suppress) {
			return '';
		}

		$ppgno = $ppgend - $ppgstart + $offset;
		$ppgno = $this->_getStyledNumber($ppgno, $type);

		if ($extras) {
			$ppgno = $this->pagenumPrefix . $ppgno . $this->pagenumSuffix;
		}

		return $ppgno;
	}

	// mPDF 6
	function _getStyledNumber($ppgno, $type, $listmarker = false)
	{
		if ($listmarker) {
			$reverse = true; // Reverse RTL numerals (Hebrew) when using for list
			$checkfont = true; // Using list - font is set, so check if character is available
		} else {
			$reverse = false; // For pagenumbers, RTL numerals (Hebrew) will get reversed later by bidi
			$checkfont = false; // For pagenumbers - font is not set, so no check
		}

		$decToAlpha = new Conversion\DecToAlpha();
		$decToCjk = new Conversion\DecToCjk();
		$decToHebrew = new Conversion\DecToHebrew();
		$decToRoman = new Conversion\DecToRoman();
		$decToOther = new Conversion\DecToOther($this);

		$lowertype = strtolower($type);

		if ($lowertype == 'upper-latin' || $lowertype == 'upper-alpha' || $type == 'A') {

			$ppgno = $decToAlpha->convert($ppgno, true);

		} elseif ($lowertype == 'lower-latin' || $lowertype == 'lower-alpha' || $type == 'a') {

			$ppgno = $decToAlpha->convert($ppgno, false);

		} elseif ($lowertype == 'upper-roman' || $type == 'I') {

			$ppgno = $decToRoman->convert($ppgno, true);

		} elseif ($lowertype == 'lower-roman' || $type == 'i') {

			$ppgno = $decToRoman->convert($ppgno, false);

		} elseif ($lowertype == 'hebrew') {

			$ppgno = $decToHebrew->convert($ppgno, $reverse);

		} elseif (preg_match('/(arabic-indic|bengali|devanagari|gujarati|gurmukhi|kannada|malayalam|oriya|persian|tamil|telugu|thai|urdu|cambodian|khmer|lao|myanmar)/i', $lowertype, $m)) {

			$cp = $decToOther->getCodePage($m[1]);
			$ppgno = $decToOther->convert($ppgno, $cp, $checkfont);

		} elseif ($lowertype == 'cjk-decimal') {

			$ppgno = $decToCjk->convert($ppgno);

		}

		return $ppgno;
	}

	function docPageSettings($num = 0)
	{
		// Returns current type (numberstyle), suppression state for this page number;
		// reset is only returned if set for this page number
		if ($num < 1) {
			$num = $this->page;
		}

		$type = $this->defaultPageNumStyle; // set default Page Number Style
		$ppgno = $num;
		$suppress = 0;
		$offset = 0;
		$reset = '';

		foreach ($this->PageNumSubstitutions as $psarr) {
			if ($num >= $psarr['from']) {
				if ($psarr['reset']) {
					if ($psarr['reset'] > 1) {
						$offset = $psarr['reset'] - 1;
					}
					$ppgno = $num - $psarr['from'] + 1 + $offset;
				}
				if ($psarr['type']) {
					$type = $psarr['type'];
				}
				if (strtoupper($psarr['suppress']) == 'ON' || $psarr['suppress'] == 1) {
					$suppress = 1;
				} elseif (strtoupper($psarr['suppress']) == 'OFF') {
					$suppress = 0;
				}
			}
			if ($num == $psarr['from']) {
				$reset = $psarr['reset'];
			}
		}

		if ($suppress) {
			$suppress = 'on';
		} else {
			$suppress = 'off';
		}

		return [$type, $suppress, $reset];
	}

	function RestartDocTemplate()
	{
		$this->docTemplateStart = $this->page;
	}

	// Page header
	function Header($content = '')
	{

		$this->cMarginL = 0;
		$this->cMarginR = 0;


		if (($this->mirrorMargins && ($this->page % 2 == 0) && $this->HTMLHeaderE) || ($this->mirrorMargins && ($this->page % 2 == 1) && $this->HTMLHeader) || (!$this->mirrorMargins && $this->HTMLHeader)) {
			$this->writeHTMLHeaders();
			return;
		}
	}

	/* -- TABLES -- */
	function TableHeaderFooter($content = '', $tablestartpage = '', $tablestartcolumn = '', $horf = 'H', $level = 0, $firstSpread = true, $finalSpread = true)
	{
		if (($horf == 'H' || $horf == 'F') && !empty($content)) { // mPDF 5.7.2
			$table = &$this->table[1][1];

			// mPDF 5.7.2
			if ($horf == 'F') { // Table Footer
				$firstrow = count($table['cells']) - $table['footernrows'];
				$lastrow = count($table['cells']) - 1;
			} else {  // Table Header
				$firstrow = 0;
				$lastrow = $table['headernrows'] - 1;
			}
			if (empty($content[$firstrow])) {
				if ($this->debug) {
					throw new \Mpdf\MpdfException("<tfoot> must precede <tbody> in a table");
				} else {
					return;
				}
			}


			// Advance down page by half width of top border
			if ($horf == 'H') { // Only if header
				if ($table['borders_separate']) {
					$adv = $table['border_spacing_V'] / 2 + $table['border_details']['T']['w'] + $table['padding']['T'];
				} else {
					$adv = $table['max_cell_border_width']['T'] / 2;
				}
				if ($adv) {
					if ($this->table_rotate) {
						$this->y += ($adv);
					} else {
						$this->DivLn($adv, $this->blklvl, true);
					}
				}
			}

			$topy = $content[$firstrow][0]['y'] - $this->y;

			for ($i = $firstrow; $i <= $lastrow; $i++) {
				$y = $this->y;

				/* -- COLUMNS -- */
				// If outside columns, this is done in PaintDivBB
				if ($this->ColActive) {
					// OUTER FILL BGCOLOR of DIVS
					if ($this->blklvl > 0) {
						$firstblockfill = $this->GetFirstBlockFill();
						if ($firstblockfill && $this->blklvl >= $firstblockfill) {
							$divh = $content[$i][0]['h'];
							$bak_x = $this->x;
							$this->DivLn($divh, -3, false);
							// Reset current block fill
							$bcor = $this->blk[$this->blklvl]['bgcolorarray'];
							$this->SetFColor($bcor);
							$this->x = $bak_x;
						}
					}
				}
				/* -- END COLUMNS -- */

				$colctr = 0;
				foreach ($content[$i] as $tablehf) {
					$colctr++;
					$y = Arrays::get($tablehf, 'y', null) - $topy;
					$this->y = $y;
					// Set some cell values
					$x = Arrays::get($tablehf, 'x', null);
					if (($this->mirrorMargins) && ($tablestartpage == 'ODD') && (($this->page) % 2 == 0)) { // EVEN
						$x = $x + $this->MarginCorrection;
					} elseif (($this->mirrorMargins) && ($tablestartpage == 'EVEN') && (($this->page) % 2 == 1)) { // ODD
						$x = $x + $this->MarginCorrection;
					}
					/* -- COLUMNS -- */
					// Added to correct for Columns
					if ($this->ColActive) {
						if ($this->directionality == 'rtl') { // *OTL*
							$x -= ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth + $this->ColGap); // *OTL*
						} // *OTL*
						else { // *OTL*
							$x += ($this->CurrCol - $tablestartcolumn) * ($this->ColWidth + $this->ColGap);
						} // *OTL*
					}
					/* -- END COLUMNS -- */

					if ($colctr == 1) {
						$x0 = $x;
					}

					// mPDF ITERATION
					if ($this->iterationCounter) {
						foreach ($tablehf['textbuffer'] as $k => $t) {
							if (!is_array($t[0]) && preg_match('/{iteration ([a-zA-Z0-9_]+)}/', $t[0], $m)) {
								$vname = '__' . $m[1] . '_';
								if (!isset($this->$vname)) {
									$this->$vname = 1;
								} else {
									$this->$vname++;
								}
								$tablehf['textbuffer'][$k][0] = preg_replace('/{iteration ' . $m[1] . '}/', $this->$vname, $tablehf['textbuffer'][$k][0]);
							}
						}
					}

					$w = Arrays::get($tablehf, 'w', null);
					$h = Arrays::get($tablehf, 'h', null);
					$va = Arrays::get($tablehf, 'va', null);
					$R = Arrays::get($tablehf, 'R', null);
					$direction = Arrays::get($tablehf, 'direction', null);
					$mih = Arrays::get($tablehf, 'mih', null);
					$border = Arrays::get($tablehf, 'border', null);
					$border_details = Arrays::get($tablehf, 'border_details', null);
					$padding = Arrays::get($tablehf, 'padding', null);
					$this->tabletheadjustfinished = true;

					$textbuffer = Arrays::get($tablehf, 'textbuffer', null);

					// Align
					$align = Arrays::get($tablehf, 'a', null);
					$this->cellTextAlign = $align;

					$this->cellLineHeight = Arrays::get($tablehf, 'cellLineHeight', null);
					$this->cellLineStackingStrategy = Arrays::get($tablehf, 'cellLineStackingStrategy', null);
					$this->cellLineStackingShift = Arrays::get($tablehf, 'cellLineStackingShift', null);

					$this->x = $x;

					if ($this->ColActive) {
						if ($table['borders_separate']) {
							$tablefill = isset($table['bgcolor'][-1]) ? $table['bgcolor'][-1] : 0;
							if ($tablefill) {
								$color = $this->colorConverter->convert($tablefill, $this->PDFAXwarnings);
								if ($color) {
									$xadj = ($table['border_spacing_H'] / 2);
									$yadj = ($table['border_spacing_V'] / 2);
									$wadj = $table['border_spacing_H'];
									$hadj = $table['border_spacing_V'];
									if ($i == $firstrow && $horf == 'H') {  // Top
										$yadj += $table['padding']['T'] + $table['border_details']['T']['w'];
										$hadj += $table['padding']['T'] + $table['border_details']['T']['w'];
									}
									if (($i == ($lastrow) || (isset($tablehf['rowspan']) && ($i + $tablehf['rowspan']) == ($lastrow + 1)) || (!isset($tablehf['rowspan']) && ($i + 1) == ($lastrow + 1))) && $horf == 'F') { // Bottom
										$hadj += $table['padding']['B'] + $table['border_details']['B']['w'];
									}
									if ($colctr == 1) {  // Left
										$xadj += $table['padding']['L'] + $table['border_details']['L']['w'];
										$wadj += $table['padding']['L'] + $table['border_details']['L']['w'];
									}
									if ($colctr == count($content[$i])) { // Right
										$wadj += $table['padding']['R'] + $table['border_details']['R']['w'];
									}
									$this->SetFColor($color);
									$this->Rect($x - $xadj, $y - $yadj, $w + $wadj, $h + $hadj, 'F');
								}
							}
						}
					}

					if ($table['empty_cells'] != 'hide' || !empty($textbuffer) || !$table['borders_separate']) {
						$paintcell = true;
					} else {
						$paintcell = false;
					}

					// Vertical align
					if ($R && intval($R) > 0 && isset($va) && $va != 'B') {
						$va = 'B';
					}

					if (!isset($va) || empty($va) || $va == 'M') {
						$this->y += ($h - $mih) / 2;
					} elseif (isset($va) && $va == 'B') {
						$this->y += $h - $mih;
					}


					// TABLE ROW OR CELL FILL BGCOLOR
					$fill = 0;
					if (isset($tablehf['bgcolor']) && $tablehf['bgcolor'] && $tablehf['bgcolor'] != 'transparent') {
						$fill = $tablehf['bgcolor'];
						$leveladj = 6;
					} elseif (isset($content[$i][0]['trbgcolor']) && $content[$i][0]['trbgcolor'] && $content[$i][0]['trbgcolor'] != 'transparent') { // Row color
						$fill = $content[$i][0]['trbgcolor'];
						$leveladj = 3;
					}
					if ($fill && $paintcell) {
						$color = $this->colorConverter->convert($fill, $this->PDFAXwarnings);
						if ($color) {
							if ($table['borders_separate']) {
								if ($this->ColActive) {
									$this->SetFColor($color);
									$this->Rect($x + ($table['border_spacing_H'] / 2), $y + ($table['border_spacing_V'] / 2), $w - $table['border_spacing_H'], $h - $table['border_spacing_V'], 'F');
								} else {
									$this->tableBackgrounds[$level * 9 + $leveladj][] = ['gradient' => false, 'x' => ($x + ($table['border_spacing_H'] / 2)), 'y' => ($y + ($table['border_spacing_V'] / 2)), 'w' => ($w - $table['border_spacing_H']), 'h' => ($h - $table['border_spacing_V']), 'col' => $color];
								}
							} else {
								if ($this->ColActive) {
									$this->SetFColor($color);
									$this->Rect($x, $y, $w, $h, 'F');
								} else {
									$this->tableBackgrounds[$level * 9 + $leveladj][] = ['gradient' => false, 'x' => $x, 'y' => $y, 'w' => $w, 'h' => $h, 'col' => $color];
								}
							}
						}
					}


					/* -- BACKGROUNDS -- */
					if (isset($tablehf['gradient']) && $tablehf['gradient'] && $paintcell) {
						$g = $this->gradient->parseBackgroundGradient($tablehf['gradient']);
						if ($g) {
							if ($table['borders_separate']) {
								$px = $x + ($table['border_spacing_H'] / 2);
								$py = $y + ($table['border_spacing_V'] / 2);
								$pw = $w - $table['border_spacing_H'];
								$ph = $h - $table['border_spacing_V'];
							} else {
								$px = $x;
								$py = $y;
								$pw = $w;
								$ph = $h;
							}
							if ($this->ColActive) {
								$this->gradient->Gradient($px, $py, $pw, $ph, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend']);
							} else {
								$this->tableBackgrounds[$level * 9 + 7][] = ['gradient' => true, 'x' => $px, 'y' => $py, 'w' => $pw, 'h' => $ph, 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => ''];
							}
						}
					}

					if (isset($tablehf['background-image']) && $paintcell) {
						if ($tablehf['background-image']['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $tablehf['background-image']['gradient'])) {
							$g = $this->gradient->parseMozGradient($tablehf['background-image']['gradient']);
							if ($g) {
								if ($table['borders_separate']) {
									$px = $x + ($table['border_spacing_H'] / 2);
									$py = $y + ($table['border_spacing_V'] / 2);
									$pw = $w - $table['border_spacing_H'];
									$ph = $h - $table['border_spacing_V'];
								} else {
									$px = $x;
									$py = $y;
									$pw = $w;
									$ph = $h;
								}
								if ($this->ColActive) {
									$this->gradient->Gradient($px, $py, $pw, $ph, $g['type'], $g['stops'], $g['colorspace'], $g['coords'], $g['extend']);
								} else {
									$this->tableBackgrounds[$level * 9 + 7][] = ['gradient' => true, 'x' => $px, 'y' => $py, 'w' => $pw, 'h' => $ph, 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => ''];
								}
							}
						} elseif ($tablehf['background-image']['image_id']) { // Background pattern
							$n = count($this->patterns) + 1;
							if ($table['borders_separate']) {
								$px = $x + ($table['border_spacing_H'] / 2);
								$py = $y + ($table['border_spacing_V'] / 2);
								$pw = $w - $table['border_spacing_H'];
								$ph = $h - $table['border_spacing_V'];
							} else {
								$px = $x;
								$py = $y;
								$pw = $w;
								$ph = $h;
							}
							if ($this->ColActive) {
								list($orig_w, $orig_h, $x_repeat, $y_repeat) = $this->_resizeBackgroundImage($tablehf['background-image']['orig_w'], $tablehf['background-image']['orig_h'], $pw, $ph, $tablehf['background-image']['resize'], $tablehf['background-image']['x_repeat'], $tablehf['background-image']['y_repeat']);
								$this->patterns[$n] = ['x' => $px, 'y' => $py, 'w' => $pw, 'h' => $ph, 'pgh' => $this->h, 'image_id' => $tablehf['background-image']['image_id'], 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $tablehf['background-image']['x_pos'], 'y_pos' => $tablehf['background-image']['y_pos'], 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'itype' => $tablehf['background-image']['itype']];
								if ($tablehf['background-image']['opacity'] > 0 && $tablehf['background-image']['opacity'] < 1) {
									$opac = $this->SetAlpha($tablehf['background-image']['opacity'], 'Normal', true);
								} else {
									$opac = '';
								}
								$this->writer->write(sprintf('q /Pattern cs /P%d scn %s %.3F %.3F %.3F %.3F re f Q', $n, $opac, $px * Mpdf::SCALE, ($this->h - $py) * Mpdf::SCALE, $pw * Mpdf::SCALE, -$ph * Mpdf::SCALE));
							} else {
								$this->tableBackgrounds[$level * 9 + 8][] = ['x' => $px, 'y' => $py, 'w' => $pw, 'h' => $ph, 'image_id' => $tablehf['background-image']['image_id'], 'orig_w' => $tablehf['background-image']['orig_w'], 'orig_h' => $tablehf['background-image']['orig_h'], 'x_pos' => $tablehf['background-image']['x_pos'], 'y_pos' => $tablehf['background-image']['y_pos'], 'x_repeat' => $tablehf['background-image']['x_repeat'], 'y_repeat' => $tablehf['background-image']['y_repeat'], 'clippath' => '', 'resize' => $tablehf['background-image']['resize'], 'opacity' => $tablehf['background-image']['opacity'], 'itype' => $tablehf['background-image']['itype']];
							}
						}
					}
					/* -- END BACKGROUNDS -- */

					// Cell Border
					if ($table['borders_separate'] && $paintcell && $border) {
						$this->_tableRect($x + ($table['border_spacing_H'] / 2) + ($border_details['L']['w'] / 2), $y + ($table['border_spacing_V'] / 2) + ($border_details['T']['w'] / 2), $w - $table['border_spacing_H'] - ($border_details['L']['w'] / 2) - ($border_details['R']['w'] / 2), $h - $table['border_spacing_V'] - ($border_details['T']['w'] / 2) - ($border_details['B']['w'] / 2), $border, $border_details, false, $table['borders_separate']);
					} elseif ($paintcell && $border) {
						$this->_tableRect($x, $y, $w, $h, $border, $border_details, true, $table['borders_separate']);   // true causes buffer
					}

					// Print cell content
					if (!empty($textbuffer)) {
						if ($horf == 'F' && preg_match('/{colsum([0-9]*)[_]*}/', $textbuffer[0][0], $m)) {
							$rep = sprintf("%01." . intval($m[1]) . "f", $this->colsums[$colctr - 1]);
							$textbuffer[0][0] = preg_replace('/{colsum[0-9_]*}/', $rep, $textbuffer[0][0]);
						}

						if ($R) {
							$cellPtSize = $textbuffer[0][11] / $this->shrin_k;
							if (!$cellPtSize) {
								$cellPtSize = $this->default_font_size;
							}
							$cellFontHeight = ($cellPtSize / Mpdf::SCALE);
							$opx = $this->x;
							$opy = $this->y;
							$angle = intval($R);

							// Only allow 45 - 90 degrees (when bottom-aligned) or -90
							if ($angle > 90) {
								$angle = 90;
							} elseif ($angle > 0 && (isset($va) && $va != 'B')) {
								$angle = 90;
							} elseif ($angle > 0 && $angle < 45) {
								$angle = 45;
							} elseif ($angle < 0) {
								$angle = -90;
							}

							$offset = ((sin(deg2rad($angle))) * 0.37 * $cellFontHeight);
							if (isset($align) && $align == 'R') {
								$this->x += ($w) + ($offset) - ($cellFontHeight / 3) - ($padding['R'] + $border_details['R']['w']);
							} elseif (!isset($align) || $align == 'C') {
								$this->x += ($w / 2) + ($offset);
							} else {
								$this->x += ($offset) + ($cellFontHeight / 3) + ($padding['L'] + $border_details['L']['w']);
							}
							$str = '';
							foreach ($tablehf['textbuffer'] as $t) {
								$str .= $t[0] . ' ';
							}
							$str = rtrim($str);

							if (!isset($va) || $va == 'M') {
								$this->y -= ($h - $mih) / 2; // Undo what was added earlier VERTICAL ALIGN
								if ($angle > 0) {
									$this->y += (($h - $mih) / 2) + ($padding['T'] + $border_details['T']['w']) + ($mih - ($padding['T'] + $border_details['T']['w'] + $border_details['B']['w'] + $padding['B']));
								} elseif ($angle < 0) {
									$this->y += (($h - $mih) / 2) + ($padding['T'] + $border_details['T']['w']);
								}
							} elseif (isset($va) && $va == 'B') {
								$this->y -= $h - $mih; // Undo what was added earlier VERTICAL ALIGN
								if ($angle > 0) {
									$this->y += $h - ($border_details['B']['w'] + $padding['B']);
								} elseif ($angle < 0) {
									$this->y += $h - $mih + ($padding['T'] + $border_details['T']['w']);
								}
							} elseif (isset($va) && $va == 'T') {
								if ($angle > 0) {
									$this->y += $mih - ($border_details['B']['w'] + $padding['B']);
								} elseif ($angle < 0) {
									$this->y += ($padding['T'] + $border_details['T']['w']);
								}
							}

							$this->Rotate($angle, $this->x, $this->y);
							$s_fs = $this->FontSizePt;
							$s_f = $this->FontFamily;
							$s_st = $this->FontStyle;
							if (!empty($textbuffer[0][3])) { // Font Color
								$cor = $textbuffer[0][3];
								$this->SetTColor($cor);
							}
							$this->SetFont($textbuffer[0][4], $textbuffer[0][2], $cellPtSize, true, true);

							$this->magic_reverse_dir($str, $this->directionality, $textbuffer[0][18]);
							$this->Text($this->x, $this->y, $str, $textbuffer[0][18], $textbuffer[0][8]); // textvar
							$this->Rotate(0);
							$this->SetFont($s_f, $s_st, $s_fs, true, true);
							$this->SetTColor(0);
							$this->x = $opx;
							$this->y = $opy;
						} else {
							if ($table['borders_separate']) { // NB twice border width
								$xadj = $border_details['L']['w'] + $padding['L'] + ($table['border_spacing_H'] / 2);
								$wadj = $border_details['L']['w'] + $border_details['R']['w'] + $padding['L'] + $padding['R'] + $table['border_spacing_H'];
								$yadj = $border_details['T']['w'] + $padding['T'] + ($table['border_spacing_H'] / 2);
							} else {
								$xadj = $border_details['L']['w'] / 2 + $padding['L'];
								$wadj = ($border_details['L']['w'] + $border_details['R']['w']) / 2 + $padding['L'] + $padding['R'];
								$yadj = $border_details['T']['w'] / 2 + $padding['T'];
							}

							$this->divwidth = $w - ($wadj);
							$this->x += $xadj;
							$this->y += $yadj;
							$this->printbuffer($textbuffer, '', true, false, $direction);
						}
					}
					$textbuffer = [];

					/* -- BACKGROUNDS -- */
					if (!$this->ColActive) {
						if (isset($content[$i][0]['trgradients']) && ($colctr == 1 || $table['borders_separate'])) {
							$g = $this->gradient->parseBackgroundGradient($content[$i][0]['trgradients']);
							if ($g) {
								$gx = $x0;
								$gy = $y;
								$gh = $h;
								$gw = $table['w'] - ($table['max_cell_border_width']['L'] / 2) - ($table['max_cell_border_width']['R'] / 2) - $table['margin']['L'] - $table['margin']['R'];
								if ($table['borders_separate']) {
									$gw -= ($table['padding']['L'] + $table['border_details']['L']['w'] + $table['padding']['R'] + $table['border_details']['R']['w'] + $table['border_spacing_H']);
									$clx = $x + ($table['border_spacing_H'] / 2);
									$cly = $y + ($table['border_spacing_V'] / 2);
									$clw = $w - $table['border_spacing_H'];
									$clh = $h - $table['border_spacing_V'];
									// Set clipping path
									$s = $this->_setClippingPath($clx, $cly, $clw, $clh); // mPDF 6
									$this->tableBackgrounds[$level * 9 + 4][] = ['gradient' => true, 'x' => $gx + ($table['border_spacing_H'] / 2), 'y' => $gy + ($table['border_spacing_V'] / 2), 'w' => $gw - $table['border_spacing_V'], 'h' => $gh - $table['border_spacing_H'], 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => $s];
								} else {
									$this->tableBackgrounds[$level * 9 + 4][] = ['gradient' => true, 'x' => $gx, 'y' => $gy, 'w' => $gw, 'h' => $gh, 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => ''];
								}
							}
						}

						if (isset($content[$i][0]['trbackground-images']) && ($colctr == 1 || $table['borders_separate'])) {
							if ($content[$i][0]['trbackground-images']['gradient'] && preg_match('/(-moz-)*(repeating-)*(linear|radial)-gradient/', $content[$i][0]['trbackground-images']['gradient'])) {
								$g = $this->gradient->parseMozGradient($content[$i][0]['trbackground-images']['gradient']);
								if ($g) {
									$gx = $x0;
									$gy = $y;
									$gh = $h;
									$gw = $table['w'] - ($table['max_cell_border_width']['L'] / 2) - ($table['max_cell_border_width']['R'] / 2) - $table['margin']['L'] - $table['margin']['R'];
									if ($table['borders_separate']) {
										$gw -= ($table['padding']['L'] + $table['border_details']['L']['w'] + $table['padding']['R'] + $table['border_details']['R']['w'] + $table['border_spacing_H']);
										$clx = $x + ($table['border_spacing_H'] / 2);
										$cly = $y + ($table['border_spacing_V'] / 2);
										$clw = $w - $table['border_spacing_H'];
										$clh = $h - $table['border_spacing_V'];
										// Set clipping path
										$s = $this->_setClippingPath($clx, $cly, $clw, $clh); // mPDF 6
										$this->tableBackgrounds[$level * 9 + 4][] = ['gradient' => true, 'x' => $gx + ($table['border_spacing_H'] / 2), 'y' => $gy + ($table['border_spacing_V'] / 2), 'w' => $gw - $table['border_spacing_V'], 'h' => $gh - $table['border_spacing_H'], 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => $s];
									} else {
										$this->tableBackgrounds[$level * 9 + 4][] = ['gradient' => true, 'x' => $gx, 'y' => $gy, 'w' => $gw, 'h' => $gh, 'gradtype' => $g['type'], 'stops' => $g['stops'], 'colorspace' => $g['colorspace'], 'coords' => $g['coords'], 'extend' => $g['extend'], 'clippath' => ''];
									}
								}
							} else {
								$image_id = $content[$i][0]['trbackground-images']['image_id'];
								$orig_w = $content[$i][0]['trbackground-images']['orig_w'];
								$orig_h = $content[$i][0]['trbackground-images']['orig_h'];
								$x_pos = $content[$i][0]['trbackground-images']['x_pos'];
								$y_pos = $content[$i][0]['trbackground-images']['y_pos'];
								$x_repeat = $content[$i][0]['trbackground-images']['x_repeat'];
								$y_repeat = $content[$i][0]['trbackground-images']['y_repeat'];
								$resize = $content[$i][0]['trbackground-images']['resize'];
								$opacity = $content[$i][0]['trbackground-images']['opacity'];
								$itype = $content[$i][0]['trbackground-images']['itype'];

								$clippath = '';
								$gx = $x0;
								$gy = $y;
								$gh = $h;
								$gw = $table['w'] - ($table['max_cell_border_width']['L'] / 2) - ($table['max_cell_border_width']['R'] / 2) - $table['margin']['L'] - $table['margin']['R'];
								if ($table['borders_separate']) {
									$gw -= ($table['padding']['L'] + $table['border_details']['L']['w'] + $table['padding']['R'] + $table['border_details']['R']['w'] + $table['border_spacing_H']);
									$clx = $x + ($table['border_spacing_H'] / 2);
									$cly = $y + ($table['border_spacing_V'] / 2);
									$clw = $w - $table['border_spacing_H'];
									$clh = $h - $table['border_spacing_V'];
									// Set clipping path
									$s = $this->_setClippingPath($clx, $cly, $clw, $clh); // mPDF 6
									$this->tableBackgrounds[$level * 9 + 5][] = ['x' => $gx + ($table['border_spacing_H'] / 2), 'y' => $gy + ($table['border_spacing_V'] / 2), 'w' => $gw - $table['border_spacing_V'], 'h' => $gh - $table['border_spacing_H'], 'image_id' => $image_id, 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $x_pos, 'y_pos' => $y_pos, 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'clippath' => $s, 'resize' => $resize, 'opacity' => $opacity, 'itype' => $itype];
								} else {
									$this->tableBackgrounds[$level * 9 + 5][] = ['x' => $gx, 'y' => $gy, 'w' => $gw, 'h' => $gh, 'image_id' => $image_id, 'orig_w' => $orig_w, 'orig_h' => $orig_h, 'x_pos' => $x_pos, 'y_pos' => $y_pos, 'x_repeat' => $x_repeat, 'y_repeat' => $y_repeat, 'clippath' => '', 'resize' => $resize, 'opacity' => $opacity, 'itype' => $itype];
								}
							}
						}
					}
					/* -- END BACKGROUNDS -- */

					// TABLE BORDER - if separate OR collapsed and only table border
					if (($table['borders_separate'] || ($this->simpleTables && !$table['simple']['border'])) && $table['border']) {
						$halfspaceL = $table['padding']['L'] + ($table['border_spacing_H'] / 2);
						$halfspaceR = $table['padding']['R'] + ($table['border_spacing_H'] / 2);
						$halfspaceT = $table['padding']['T'] + ($table['border_spacing_V'] / 2);
						$halfspaceB = $table['padding']['B'] + ($table['border_spacing_V'] / 2);
						$tbx = $x;
						$tby = $y;
						$tbw = $w;
						$tbh = $h;
						$tab_bord = 0;
						$corner = '';
						if ($i == $firstrow && $horf == 'H') {  // Top
							$tby -= $halfspaceT + ($table['border_details']['T']['w'] / 2);
							$tbh += $halfspaceT + ($table['border_details']['T']['w'] / 2);
							$this->setBorder($tab_bord, Border::TOP);
							$corner .= 'T';
						}
						if (($i == ($lastrow) || (isset($tablehf['rowspan']) && ($i + $tablehf['rowspan']) == ($lastrow + 1))) && $horf == 'F') { // Bottom
							$tbh += $halfspaceB + ($table['border_details']['B']['w'] / 2);
							$this->setBorder($tab_bord, Border::BOTTOM);
							$corner .= 'B';
						}
						if ($colctr == 1 && $firstSpread) { // Left
							$tbx -= $halfspaceL + ($table['border_details']['L']['w'] / 2);
							$tbw += $halfspaceL + ($table['border_details']['L']['w'] / 2);
							$this->setBorder($tab_bord, Border::LEFT);
							$corner .= 'L';
						}
						if ($colctr == count($content[$i]) && $finalSpread) { // Right
							$tbw += $halfspaceR + ($table['border_details']['R']['w'] / 2);
							$this->setBorder($tab_bord, Border::RIGHT);
							$corner .= 'R';
						}
						$this->_tableRect($tbx, $tby, $tbw, $tbh, $tab_bord, $table['border_details'], false, $table['borders_separate'], 'table', $corner, $table['border_spacing_V'], $table['border_spacing_H']);
					}
				}// end column $content
				$this->y = $y + $h; // Update y coordinate
			}// end row $i
			unset($table);
			$this->colsums = [];
		}
	}

	/* -- END TABLES -- */

	function SetHTMLHeader($header = '', $OE = '', $write = false)
	{

		$height = 0;
		if (is_array($header) && isset($header['html']) && $header['html']) {
			$Hhtml = $header['html'];
			if ($this->setAutoTopMargin) {
				if (isset($header['h'])) {
					$height = $header['h'];
				} else {
					$height = $this->_getHtmlHeight($Hhtml);
				}
			}
		} elseif (!is_array($header) && $header) {
			$Hhtml = $header;
			if ($this->setAutoTopMargin) {
				$height = $this->_getHtmlHeight($Hhtml);
			}
		} else {
			$Hhtml = '';
		}

		if ($OE !== 'E') {
			$OE = 'O';
		}

		if ($OE === 'E') {
			if ($Hhtml) {
				$this->HTMLHeaderE = [];
				$this->HTMLHeaderE['html'] = $Hhtml;
				$this->HTMLHeaderE['h'] = $height;
			} else {
				$this->HTMLHeaderE = '';
			}
		} else {
			if ($Hhtml) {
				$this->HTMLHeader = [];
				$this->HTMLHeader['html'] = $Hhtml;
				$this->HTMLHeader['h'] = $height;
			} else {
				$this->HTMLHeader = '';
			}
		}

		if (!$this->mirrorMargins && $OE == 'E') {
			return;
		}
		if ($Hhtml == '') {
			return;
		}

		if ($this->setAutoTopMargin == 'pad') {
			$this->tMargin = $this->margin_header + $height + $this->orig_tMargin;
			if (isset($this->saveHTMLHeader[$this->page][$OE]['mt'])) {
				$this->saveHTMLHeader[$this->page][$OE]['mt'] = $this->tMargin;
			}
		} elseif ($this->setAutoTopMargin == 'stretch') {
			$this->tMargin = max($this->orig_tMargin, $this->margin_header + $height + $this->autoMarginPadding);
			if (isset($this->saveHTMLHeader[$this->page][$OE]['mt'])) {
				$this->saveHTMLHeader[$this->page][$OE]['mt'] = $this->tMargin;
			}
		}
		if ($write && $this->state != 0 && (($this->mirrorMargins && $OE == 'E' && ($this->page) % 2 == 0) || ($this->mirrorMargins && $OE != 'E' && ($this->page) % 2 == 1) || !$this->mirrorMargins)) {
			$this->writeHTMLHeaders();
		}
	}

	function SetHTMLFooter($footer = '', $OE = '')
	{
		$height = 0;
		if (is_array($footer) && isset($footer['html']) && $footer['html']) {
			$Fhtml = $footer['html'];
			if ($this->setAutoBottomMargin) {
				if (isset($footer['h'])) {
					$height = $footer['h'];
				} else {
					$height = $this->_getHtmlHeight($Fhtml);
				}
			}
		} elseif (!is_array($footer) && $footer) {
			$Fhtml = $footer;
			if ($this->setAutoBottomMargin) {
				$height = $this->_getHtmlHeight($Fhtml);
			}
		} else {
			$Fhtml = '';
		}

		if ($OE !== 'E') {
			$OE = 'O';
		}

		if ($OE === 'E') {
			if ($Fhtml) {
				$this->HTMLFooterE = [];
				$this->HTMLFooterE['html'] = $Fhtml;
				$this->HTMLFooterE['h'] = $height;
			} else {
				$this->HTMLFooterE = '';
			}
		} else {
			if ($Fhtml) {
				$this->HTMLFooter = [];
				$this->HTMLFooter['html'] = $Fhtml;
				$this->HTMLFooter['h'] = $height;
			} else {
				$this->HTMLFooter = '';
			}
		}

		if (!$this->mirrorMargins && $OE == 'E') {
			return;
		}

		if ($Fhtml == '') {
			return false;
		}

		if ($this->setAutoBottomMargin == 'pad') {
			$this->bMargin = $this->margin_footer + $height + $this->orig_bMargin;
			$this->PageBreakTrigger = $this->h - $this->bMargin;
			if (isset($this->saveHTMLHeader[$this->page][$OE]['mb'])) {
				$this->saveHTMLHeader[$this->page][$OE]['mb'] = $this->bMargin;
			}
		} elseif ($this->setAutoBottomMargin == 'stretch') {
			$this->bMargin = max($this->orig_bMargin, $this->margin_footer + $height + $this->autoMarginPadding);
			$this->PageBreakTrigger = $this->h - $this->bMargin;
			if (isset($this->saveHTMLHeader[$this->page][$OE]['mb'])) {
				$this->saveHTMLHeader[$this->page][$OE]['mb'] = $this->bMargin;
			}
		}
	}

	function _getHtmlHeight($html)
	{
		$save_state = $this->state;
		if ($this->state == 0) {
			$this->AddPage($this->CurOrientation);
		}
		$this->state = 2;
		$this->Reset();
		$this->pageoutput[$this->page] = [];
		$save_x = $this->x;
		$save_y = $this->y;
		$this->x = $this->lMargin;
		$this->y = $this->margin_header;

		// Replace of page number aliases and date format
		$pnstr = $this->pagenumPrefix . $this->docPageNum($this->page) . $this->pagenumSuffix;
		$pntstr = $this->nbpgPrefix . $this->docPageNumTotal($this->page) . $this->nbpgSuffix;
		$nb = $this->page;
		$html = $this->aliasReplace($html, $pnstr, $pntstr, $nb);

		$this->HTMLheaderPageLinks = [];
		$this->HTMLheaderPageAnnots = [];
		$this->HTMLheaderPageForms = [];
		$savepb = $this->pageBackgrounds;
		$this->writingHTMLheader = true;
		$this->WriteHTML($html, HTMLParserMode::HTML_HEADER_BUFFER);
		$this->writingHTMLheader = false;
		$h = ($this->y - $this->margin_header);
		$this->Reset();

		// mPDF 5.7.2 - Clear in case Float used in Header/Footer
		$this->blk[0]['blockContext'] = 0;
		$this->blk[0]['float_endpos'] = 0;

		$this->pageoutput[$this->page] = [];
		$this->headerbuffer = '';
		$this->pageBackgrounds = $savepb;
		$this->x = $save_x;
		$this->y = $save_y;
		$this->state = $save_state;

		if ($save_state == 0) {
			unset($this->pages[1]);
			$this->page = 0;
		}
		return $h;
	}

	// Called internally from Header
	function writeHTMLHeaders()
	{

		if ($this->mirrorMargins && ($this->page) % 2 == 0) {
			$OE = 'E';
		} else {
			$OE = 'O';
		}

		if ($OE === 'E') {
			$this->saveHTMLHeader[$this->page][$OE]['html'] = $this->HTMLHeaderE['html'];
		} else {
			$this->saveHTMLHeader[$this->page][$OE]['html'] = $this->HTMLHeader['html'];
		}

		if ($this->forcePortraitHeaders && $this->CurOrientation == 'L' && $this->CurOrientation != $this->DefOrientation) {
			$this->saveHTMLHeader[$this->page][$OE]['rotate'] = true;
			$this->saveHTMLHeader[$this->page][$OE]['ml'] = $this->tMargin;
			$this->saveHTMLHeader[$this->page][$OE]['mr'] = $this->bMargin;
			$this->saveHTMLHeader[$this->page][$OE]['mh'] = $this->margin_header;
			$this->saveHTMLHeader[$this->page][$OE]['mf'] = $this->margin_footer;
			$this->saveHTMLHeader[$this->page][$OE]['pw'] = $this->h;
			$this->saveHTMLHeader[$this->page][$OE]['ph'] = $this->w;
		} else {
			$this->saveHTMLHeader[$this->page][$OE]['ml'] = $this->lMargin;
			$this->saveHTMLHeader[$this->page][$OE]['mr'] = $this->rMargin;
			$this->saveHTMLHeader[$this->page][$OE]['mh'] = $this->margin_header;
			$this->saveHTMLHeader[$this->page][$OE]['mf'] = $this->margin_footer;
			$this->saveHTMLHeader[$this->page][$OE]['pw'] = $this->w;
			$this->saveHTMLHeader[$this->page][$OE]['ph'] = $this->h;
		}
	}

	function writeHTMLFooters()
	{

		if ($this->mirrorMargins && ($this->page) % 2 == 0) {
			$OE = 'E';
		} else {
			$OE = 'O';
		}

		if ($OE === 'E') {
			$this->saveHTMLFooter[$this->page][$OE]['html'] = $this->HTMLFooterE['html'];
		} else {
			$this->saveHTMLFooter[$this->page][$OE]['html'] = $this->HTMLFooter['html'];
		}

		if ($this->forcePortraitHeaders && $this->CurOrientation == 'L' && $this->CurOrientation != $this->DefOrientation) {
			$this->saveHTMLFooter[$this->page][$OE]['rotate'] = true;
			$this->saveHTMLFooter[$this->page][$OE]['ml'] = $this->tMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mr'] = $this->bMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mt'] = $this->rMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mb'] = $this->lMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mh'] = $this->margin_header;
			$this->saveHTMLFooter[$this->page][$OE]['mf'] = $this->margin_footer;
			$this->saveHTMLFooter[$this->page][$OE]['pw'] = $this->h;
			$this->saveHTMLFooter[$this->page][$OE]['ph'] = $this->w;
		} else {
			$this->saveHTMLFooter[$this->page][$OE]['ml'] = $this->lMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mr'] = $this->rMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mt'] = $this->tMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mb'] = $this->bMargin;
			$this->saveHTMLFooter[$this->page][$OE]['mh'] = $this->margin_header;
			$this->saveHTMLFooter[$this->page][$OE]['mf'] = $this->margin_footer;
			$this->saveHTMLFooter[$this->page][$OE]['pw'] = $this->w;
			$this->saveHTMLFooter[$this->page][$OE]['ph'] = $this->h;
		}
	}

	// mPDF 6
	function _shareHeaderFooterWidth($cl, $cc, $cr)
	{
	// mPDF 6
		$l = mb_strlen($cl, 'UTF-8');
		$c = mb_strlen($cc, 'UTF-8');
		$r = mb_strlen($cr, 'UTF-8');
		$s = max($l, $r);
		$tw = $c + 2 * $s;
		if ($tw > 0) {
			return [intval($s * 100 / $tw), intval($c * 100 / $tw), intval($s * 100 / $tw)];
		} else {
			return [33, 33, 33];
		}
	}

	// mPDF 6
	// Create an HTML header/footer from array (non-HTML header/footer)
	function _createHTMLheaderFooter($arr, $hf)
	{
		$lContent = (isset($arr['L']['content']) ? $arr['L']['content'] : '');
		$cContent = (isset($arr['C']['content']) ? $arr['C']['content'] : '');
		$rContent = (isset($arr['R']['content']) ? $arr['R']['content'] : '');

		list($lw, $cw, $rw) = $this->_shareHeaderFooterWidth($lContent, $cContent, $rContent);

		if ($hf == 'H') {
			$valign = 'bottom';
			$vpadding = '0 0 ' . $this->header_line_spacing . 'em 0';
		} else {
			$valign = 'top';
			$vpadding = '' . $this->footer_line_spacing . 'em 0 0 0';
		}

		if ($this->directionality == 'rtl') { // table columns get reversed so need different text-alignment
			$talignL = 'right';
			$talignR = 'left';
		} else {
			$talignL = 'left';
			$talignR = 'right';
		}

		$html = '<table width="100%" style="border-collapse: collapse; margin: 0; vertical-align: ' . $valign . '; color: #000000; ';

		if (isset($arr['line']) && $arr['line']) {
			$html .= ' border-' . $valign . ': 0.1mm solid #000000;';
		}

		$html .= '">';
		$html .= '<tr>';
		$html .= '<td width="' . $lw . '%" style="padding: ' . $vpadding . '; text-align: ' . $talignL . '; ';

		if (isset($arr['L']['font-family'])) {
			$html .= ' font-family: ' . $arr['L']['font-family'] . ';';
		}

		if (isset($arr['L']['color'])) {
			$html .= ' color: ' . $arr['L']['color'] . ';';
		}

		if (isset($arr['L']['font-size'])) {
			$html .= ' font-size: ' . $arr['L']['font-size'] . 'pt;';
		}

		if (isset($arr['L']['font-style'])) {
			if ($arr['L']['font-style'] == 'B' || $arr['L']['font-style'] == 'BI') {
				$html .= ' font-weight: bold;';
			}
			if ($arr['L']['font-style'] == 'I' || $arr['L']['font-style'] == 'BI') {
				$html .= ' font-style: italic;';
			}
		}

		$html .= '">' . $lContent . '</td>';
		$html .= '<td width="' . $cw . '%" style="padding: ' . $vpadding . '; text-align: center; ';

		if (isset($arr['C']['font-family'])) {
			$html .= ' font-family: ' . $arr['C']['font-family'] . ';';
		}

		if (isset($arr['C']['color'])) {
			$html .= ' color: ' . $arr['C']['color'] . ';';
		}

		if (isset($arr['C']['font-size'])) {
			$html .= ' font-size: ' . $arr['C']['font-size'] . 'pt;';
		}

		if (isset($arr['C']['font-style'])) {
			if ($arr['C']['font-style'] == 'B' || $arr['C']['font-style'] == 'BI') {
				$html .= ' font-weight: bold;';
			}
			if ($arr['C']['font-style'] == 'I' || $arr['C']['font-style'] == 'BI') {
				$html .= ' font-style: italic;';
			}
		}

		$html .= '">' . $cContent . '</td>';
		$html .= '<td width="' . $rw . '%" style="padding: ' . $vpadding . '; text-align: ' . $talignR . '; ';

		if (isset($arr['R']['font-family'])) {
			$html .= ' font-family: ' . $arr['R']['font-family'] . ';';
		}

		if (isset($arr['R']['color'])) {
			$html .= ' color: ' . $arr['R']['color'] . ';';
		}

		if (isset($arr['R']['font-size'])) {
			$html .= ' font-size: ' . $arr['R']['font-size'] . 'pt;';
		}

		if (isset($arr['R']['font-style'])) {
			if ($arr['R']['font-style'] == 'B' || $arr['R']['font-style'] == 'BI') {
				$html .= ' font-weight: bold;';
			}
			if ($arr['R']['font-style'] == 'I' || $arr['R']['font-style'] == 'BI') {
				$html .= ' font-style: italic;';
			}
		}

		$html .= '">' . $rContent . '</td>';
		$html .= '</tr></table>';

		return $html;
	}

	function DefHeaderByName($name, $arr)
	{
		if (!$name) {
			$name = '_nonhtmldefault';
		}
		$html = $this->_createHTMLheaderFooter($arr, 'H');

		$this->pageHTMLheaders[$name]['html'] = $html;
		$this->pageHTMLheaders[$name]['h'] = $this->_getHtmlHeight($html);
	}

	function DefFooterByName($name, $arr)
	{
		if (!$name) {
			$name = '_nonhtmldefault';
		}
		$html = $this->_createHTMLheaderFooter($arr, 'F');

		$this->pageHTMLfooters[$name]['html'] = $html;
		$this->pageHTMLfooters[$name]['h'] = $this->_getHtmlHeight($html);
	}

	function SetHeaderByName($name, $side = 'O', $write = false)
	{
		if (!$name) {
			$name = '_nonhtmldefault';
		}
		$this->SetHTMLHeader($this->pageHTMLheaders[$name], $side, $write);
	}

	function SetFooterByName($name, $side = 'O')
	{
		if (!$name) {
			$name = '_nonhtmldefault';
		}
		$this->SetHTMLFooter($this->pageHTMLfooters[$name], $side);
	}

	function DefHTMLHeaderByName($name, $html)
	{
		if (!$name) {
			$name = '_default';
		}

		$this->pageHTMLheaders[$name]['html'] = $html;
		$this->pageHTMLheaders[$name]['h'] = $this->_getHtmlHeight($html);
	}

	function DefHTMLFooterByName($name, $html)
	{
		if (!$name) {
			$name = '_default';
		}

		$this->pageHTMLfooters[$name]['html'] = $html;
		$this->pageHTMLfooters[$name]['h'] = $this->_getHtmlHeight($html);
	}

	function SetHTMLHeaderByName($name, $side = 'O', $write = false)
	{
		if (!$name) {
			$name = '_default';
		}
		$this->SetHTMLHeader($this->pageHTMLheaders[$name], $side, $write);
	}

	function SetHTMLFooterByName($name, $side = 'O')
	{
		if (!$name) {
			$name = '_default';
		}
		$this->SetHTMLFooter($this->pageHTMLfooters[$name], $side);
	}

	function SetHeader($Harray = [], $side = '', $write = false)
	{
		$oddhtml = '';
		$evenhtml = '';

		if (is_string($Harray)) {

			if (strlen($Harray) === 0) {

				$oddhtml = '';
				$evenhtml = '';

			} elseif (strpos($Harray, '|') !== false) {

				$hdet = explode('|', $Harray);

				list($lw, $cw, $rw) = $this->_shareHeaderFooterWidth($hdet[0], $hdet[1], $hdet[2]);
				$oddhtml = '<table width="100%" style="border-collapse: collapse; margin: 0; vertical-align: bottom; color: #000000; ';

				if ($this->defaultheaderfontsize) {
					$oddhtml .= ' font-size: ' . $this->defaultheaderfontsize . 'pt;';
				}

				if ($this->defaultheaderfontstyle) {

					if ($this->defaultheaderfontstyle == 'B' || $this->defaultheaderfontstyle == 'BI') {
						$oddhtml .= ' font-weight: bold;';
					}

					if ($this->defaultheaderfontstyle == 'I' || $this->defaultheaderfontstyle == 'BI') {
						$oddhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultheaderline) {
					$oddhtml .= ' border-bottom: 0.1mm solid #000000;';
				}

				$oddhtml .= '">';
				$oddhtml .= '<tr>';
				$oddhtml .= '<td width="' . $lw . '%" style="padding: 0 0 ' . $this->header_line_spacing . 'em 0; text-align: left; ">' . $hdet[0] . '</td>';
				$oddhtml .= '<td width="' . $cw . '%" style="padding: 0 0 ' . $this->header_line_spacing . 'em 0; text-align: center; ">' . $hdet[1] . '</td>';
				$oddhtml .= '<td width="' . $rw . '%" style="padding: 0 0 ' . $this->header_line_spacing . 'em 0; text-align: right; ">' . $hdet[2] . '</td>';
				$oddhtml .= '</tr></table>';

				$evenhtml = '<table width="100%" style="border-collapse: collapse; margin: 0; vertical-align: bottom; color: #000000; ';

				if ($this->defaultheaderfontsize) {
					$evenhtml .= ' font-size: ' . $this->defaultheaderfontsize . 'pt;';
				}

				if ($this->defaultheaderfontstyle) {
					if ($this->defaultheaderfontstyle == 'B' || $this->defaultheaderfontstyle == 'BI') {
						$evenhtml .= ' font-weight: bold;';
					}
					if ($this->defaultheaderfontstyle == 'I' || $this->defaultheaderfontstyle == 'BI') {
						$evenhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultheaderline) {
					$evenhtml .= ' border-bottom: 0.1mm solid #000000;';
				}

				$evenhtml .= '">';
				$evenhtml .= '<tr>';
				$evenhtml .= '<td width="' . $rw . '%" style="padding: 0 0 ' . $this->header_line_spacing . 'em 0; text-align: left; ">' . $hdet[2] . '</td>';
				$evenhtml .= '<td width="' . $cw . '%" style="padding: 0 0 ' . $this->header_line_spacing . 'em 0; text-align: center; ">' . $hdet[1] . '</td>';
				$evenhtml .= '<td width="' . $lw . '%" style="padding: 0 0 ' . $this->header_line_spacing . 'em 0; text-align: right; ">' . $hdet[0] . '</td>';
				$evenhtml .= '</tr></table>';

			} else {

				$oddhtml = '<div style="margin: 0; color: #000000; ';

				if ($this->defaultheaderfontsize) {
					$oddhtml .= ' font-size: ' . $this->defaultheaderfontsize . 'pt;';
				}

				if ($this->defaultheaderfontstyle) {

					if ($this->defaultheaderfontstyle == 'B' || $this->defaultheaderfontstyle == 'BI') {
						$oddhtml .= ' font-weight: bold;';
					}

					if ($this->defaultheaderfontstyle == 'I' || $this->defaultheaderfontstyle == 'BI') {
						$oddhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultheaderline) {
					$oddhtml .= ' border-bottom: 0.1mm solid #000000;';
				}

				$oddhtml .= 'text-align: right; ">' . $Harray . '</div>';
				$evenhtml = '<div style="margin: 0; color: #000000; ';

				if ($this->defaultheaderfontsize) {
					$evenhtml .= ' font-size: ' . $this->defaultheaderfontsize . 'pt;';
				}

				if ($this->defaultheaderfontstyle) {

					if ($this->defaultheaderfontstyle == 'B' || $this->defaultheaderfontstyle == 'BI') {
						$evenhtml .= ' font-weight: bold;';
					}

					if ($this->defaultheaderfontstyle == 'I' || $this->defaultheaderfontstyle == 'BI') {
						$evenhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultheaderline) {
					$evenhtml .= ' border-bottom: 0.1mm solid #000000;';
				}

				$evenhtml .= 'text-align: left; ">' . $Harray . '</div>';
			}

		} elseif (is_array($Harray) && !empty($Harray)) {

			$odd = null;
			$even = null;

			if ($side === 'O') {
				$odd = $Harray;
			} elseif ($side === 'E') {
				$even = $Harray;
			} else {
				$odd = Arrays::get($Harray, 'odd', null);
				$even = Arrays::get($Harray, 'even', null);
			}

			$oddhtml = $this->_createHTMLheaderFooter($odd, 'H');
			$evenhtml = $this->_createHTMLheaderFooter($even, 'H');
		}

		if ($side === 'E') {
			$this->SetHTMLHeader($evenhtml, 'E', $write);
		} elseif ($side === 'O') {
			$this->SetHTMLHeader($oddhtml, 'O', $write);
		} else {
			$this->SetHTMLHeader($oddhtml, 'O', $write);
			$this->SetHTMLHeader($evenhtml, 'E', $write);
		}
	}

	function SetFooter($Farray = [], $side = '')
	{
		$oddhtml = '';
		$evenhtml = '';

		if (is_string($Farray)) {

			if (strlen($Farray) == 0) {

				$oddhtml = '';
				$evenhtml = '';

			} elseif (strpos($Farray, '|') !== false) {

				$hdet = explode('|', $Farray);
				$oddhtml = '<table width="100%" style="border-collapse: collapse; margin: 0; vertical-align: top; color: #000000; ';

				if ($this->defaultfooterfontsize) {
					$oddhtml .= ' font-size: ' . $this->defaultfooterfontsize . 'pt;';
				}

				if ($this->defaultfooterfontstyle) {
					if ($this->defaultfooterfontstyle == 'B' || $this->defaultfooterfontstyle == 'BI') {
						$oddhtml .= ' font-weight: bold;';
					}
					if ($this->defaultfooterfontstyle == 'I' || $this->defaultfooterfontstyle == 'BI') {
						$oddhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultfooterline) {
					$oddhtml .= ' border-top: 0.1mm solid #000000;';
				}

				$oddhtml .= '">';
				$oddhtml .= '<tr>';
				$oddhtml .= '<td width="33%" style="padding: ' . $this->footer_line_spacing . 'em 0 0 0; text-align: left; ">' . $hdet[0] . '</td>';
				$oddhtml .= '<td width="33%" style="padding: ' . $this->footer_line_spacing . 'em 0 0 0; text-align: center; ">' . $hdet[1] . '</td>';
				$oddhtml .= '<td width="33%" style="padding: ' . $this->footer_line_spacing . 'em 0 0 0; text-align: right; ">' . $hdet[2] . '</td>';
				$oddhtml .= '</tr></table>';

				$evenhtml = '<table width="100%" style="border-collapse: collapse; margin: 0; vertical-align: top; color: #000000; ';

				if ($this->defaultfooterfontsize) {
					$evenhtml .= ' font-size: ' . $this->defaultfooterfontsize . 'pt;';
				}

				if ($this->defaultfooterfontstyle) {

					if ($this->defaultfooterfontstyle == 'B' || $this->defaultfooterfontstyle == 'BI') {
						$evenhtml .= ' font-weight: bold;';
					}

					if ($this->defaultfooterfontstyle == 'I' || $this->defaultfooterfontstyle == 'BI') {
						$evenhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultfooterline) {
					$evenhtml .= ' border-top: 0.1mm solid #000000;';
				}

				$evenhtml .= '">';
				$evenhtml .= '<tr>';
				$evenhtml .= '<td width="33%" style="padding: ' . $this->footer_line_spacing . 'em 0 0 0; text-align: left; ">' . $hdet[2] . '</td>';
				$evenhtml .= '<td width="33%" style="padding: ' . $this->footer_line_spacing . 'em 0 0 0; text-align: center; ">' . $hdet[1] . '</td>';
				$evenhtml .= '<td width="33%" style="padding: ' . $this->footer_line_spacing . 'em 0 0 0; text-align: right; ">' . $hdet[0] . '</td>';
				$evenhtml .= '</tr></table>';

			} else {

				$oddhtml = '<div style="margin: 0; color: #000000; ';

				if ($this->defaultfooterfontsize) {
					$oddhtml .= ' font-size: ' . $this->defaultfooterfontsize . 'pt;';
				}

				if ($this->defaultfooterfontstyle) {

					if ($this->defaultfooterfontstyle == 'B' || $this->defaultfooterfontstyle == 'BI') {
						$oddhtml .= ' font-weight: bold;';
					}

					if ($this->defaultfooterfontstyle == 'I' || $this->defaultfooterfontstyle == 'BI') {
						$oddhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultfooterline) {
					$oddhtml .= ' border-top: 0.1mm solid #000000;';
				}

				$oddhtml .= 'text-align: right; ">' . $Farray . '</div>';

				$evenhtml = '<div style="margin: 0; color: #000000; ';

				if ($this->defaultfooterfontsize) {
					$evenhtml .= ' font-size: ' . $this->defaultfooterfontsize . 'pt;';
				}

				if ($this->defaultfooterfontstyle) {

					if ($this->defaultfooterfontstyle == 'B' || $this->defaultfooterfontstyle == 'BI') {
						$evenhtml .= ' font-weight: bold;';
					}

					if ($this->defaultfooterfontstyle == 'I' || $this->defaultfooterfontstyle == 'BI') {
						$evenhtml .= ' font-style: italic;';
					}
				}

				if ($this->defaultfooterline) {
					$evenhtml .= ' border-top: 0.1mm solid #000000;';
				}

				$evenhtml .= 'text-align: left; ">' . $Farray . '</div>';
			}

		} elseif (is_array($Farray)) {

			$odd = null;
			$even = null;

			if ($side === 'O') {
				$odd = $Farray;
			} elseif ($side == 'E') {
				$even = $Farray;
			} else {
				$odd = Arrays::get($Farray, 'odd', null);
				$even = Arrays::get($Farray, 'even', null);
			}

			$oddhtml = $this->_createHTMLheaderFooter($odd, 'F');
			$evenhtml = $this->_createHTMLheaderFooter($even, 'F');
		}

		if ($side === 'E') {
			$this->SetHTMLFooter($evenhtml, 'E');
		} elseif ($side === 'O') {
			$this->SetHTMLFooter($oddhtml, 'O');
		} else {
			$this->SetHTMLFooter($oddhtml, 'O');
			$this->SetHTMLFooter($evenhtml, 'E');
		}
	}

	/* -- WATERMARK -- */

	function SetWatermarkText($txt = '', $alpha = -1)
	{
		if ($txt instanceof \Mpdf\WatermarkText) {
			$this->watermarkTextObject = $txt;
			$this->watermarkText = $txt->getText();
			$this->watermarkTextAlpha = $txt->getAlpha();
			$this->watermarkAngle = $txt->getAngle();
			$this->watermark_font = $txt->getFont() === null ? $txt->getFont() : $this->watermark_font;
			$this->watermark_size = $txt->getSize();

			return;
		}

		if ($alpha >= 0) {
			$this->watermarkTextAlpha = $alpha;
		}

		$this->watermarkText = $txt;
	}

	function SetWatermarkImage($src, $alpha = -1, $size = 'D', $pos = 'F')
	{
		if ($src instanceof \Mpdf\WatermarkImage) {
			$this->watermarkImage = $src->getPath();
			$this->watermark_size = $src->getSize();
			$this->watermark_pos = $src->getPosition();
			$this->watermarkImageAlpha = $src->getAlpha();
			$this->watermarkImgBehind = $src->isBehindContent();
			$this->watermarkImgAlphaBlend = $src->getAlphaBlend();

			return;
		}

		if ($alpha >= 0) {
			$this->watermarkImageAlpha = $alpha;
		}

		$this->watermarkImage = $src;
		$this->watermark_size = $size;
		$this->watermark_pos = $pos;
	}

	/* -- END WATERMARK -- */

	// Page footer
	function Footer()
	{
		/* -- CSS-PAGE -- */
		// PAGED MEDIA - CROP / CROSS MARKS from @PAGE
		if ($this->show_marks == 'CROP' || $this->show_marks == 'CROPCROSS') {
			// Show TICK MARKS
			$this->SetLineWidth(0.1); // = 0.1 mm
			$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			$l = $this->cropMarkLength;
			$m = $this->cropMarkMargin; // Distance of crop mark from margin
			$b = $this->nonPrintMargin; // Non-printable border at edge of paper sheet
			$ax1 = $b;
			$bx = $this->page_box['outer_width_LR'] - $m;
			$ax = max($ax1, $bx - $l);
			$cx1 = $this->w - $b;
			$dx = $this->w - $this->page_box['outer_width_LR'] + $m;
			$cx = min($cx1, $dx + $l);
			$ay1 = $b;
			$by = $this->page_box['outer_width_TB'] - $m;
			$ay = max($ay1, $by - $l);
			$cy1 = $this->h - $b;
			$dy = $this->h - $this->page_box['outer_width_TB'] + $m;
			$cy = min($cy1, $dy + $l);

			$this->Line($ax, $this->page_box['outer_width_TB'], $bx, $this->page_box['outer_width_TB']);
			$this->Line($cx, $this->page_box['outer_width_TB'], $dx, $this->page_box['outer_width_TB']);
			$this->Line($ax, $this->h - $this->page_box['outer_width_TB'], $bx, $this->h - $this->page_box['outer_width_TB']);
			$this->Line($cx, $this->h - $this->page_box['outer_width_TB'], $dx, $this->h - $this->page_box['outer_width_TB']);
			$this->Line($this->page_box['outer_width_LR'], $ay, $this->page_box['outer_width_LR'], $by);
			$this->Line($this->page_box['outer_width_LR'], $cy, $this->page_box['outer_width_LR'], $dy);
			$this->Line($this->w - $this->page_box['outer_width_LR'], $ay, $this->w - $this->page_box['outer_width_LR'], $by);
			$this->Line($this->w - $this->page_box['outer_width_LR'], $cy, $this->w - $this->page_box['outer_width_LR'], $dy);

			if ($this->printers_info) {
				$hd = date('Y-m-d H:i') . '  Page ' . $this->page . ' of {nb}';
				$this->SetTColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
				$this->SetFont('arial', '', 7.5, true, true);
				$this->x = $this->page_box['outer_width_LR'] + 1.5;
				$this->y = 1;
				$this->Cell(0, $this->FontSize, $hd, 0, 0, 'L', 0, '', 0, 0, 0, 'M');
				$this->SetFont($this->default_font, '', $this->original_default_font_size);
			}
		}
		if ($this->show_marks == 'CROSS' || $this->show_marks == 'CROPCROSS') {
			$this->SetLineWidth(0.1); // = 0.1 mm
			$this->SetDColor($this->colorConverter->convert(0, $this->PDFAXwarnings));
			$l = 14 / 2; // longer length of the cross line (half)
			$w = 6 / 2; // shorter width of the cross line (half)
			$r = 1.2; // radius of circle
			$m = $this->crossMarkMargin; // Distance of cross mark from margin
			$x1 = $this->page_box['outer_width_LR'] - $m;
			$x2 = $this->w - $this->page_box['outer_width_LR'] + $m;
			$y1 = $this->page_box['outer_width_TB'] - $m;
			$y2 = $this->h - $this->page_box['outer_width_TB'] + $m;
			// Left
			$this->Circle($x1, $this->h / 2, $r, 'S');
			$this->Line($x1 - $w, $this->h / 2, $x1 + $w, $this->h / 2);
			$this->Line($x1, $this->h / 2 - $l, $x1, $this->h / 2 + $l);
			// Right
			$this->Circle($x2, $this->h / 2, $r, 'S');
			$this->Line($x2 - $w, $this->h / 2, $x2 + $w, $this->h / 2);
			$this->Line($x2, $this->h / 2 - $l, $x2, $this->h / 2 + $l);
			// Top
			$this->Circle($this->w / 2, $y1, $r, 'S');
			$this->Line($this->w / 2, $y1 - $w, $this->w / 2, $y1 + $w);
			$this->Line($this->w / 2 - $l, $y1, $this->w / 2 + $l, $y1);
			// Bottom
			$this->Circle($this->w / 2, $y2, $r, 'S');
			$this->Line($this->w / 2, $y2 - $w, $this->w / 2, $y2 + $w);
			$this->Line($this->w / 2 - $l, $y2, $this->w / 2 + $l, $y2);
		}

		/* -- END CSS-PAGE -- */

		// mPDF 6
		// If @page set non-HTML headers/footers named, they were not read until later in the HTML code - so now set them
		if ($this->page == 1) {
			if ($this->firstPageBoxHeader) {
				if (isset($this->pageHTMLheaders[$this->firstPageBoxHeader])) {
					$this->HTMLHeader = $this->pageHTMLheaders[$this->firstPageBoxHeader];
				}
				$this->Header();
			}
			if ($this->firstPageBoxFooter) {
				if (isset($this->pageHTMLfooters[$this->firstPageBoxFooter])) {
					$this->HTMLFooter = $this->pageHTMLfooters[$this->firstPageBoxFooter];
				}
			}
			$this->firstPageBoxHeader = '';
			$this->firstPageBoxFooter = '';
		}


		if (($this->mirrorMargins && ($this->page % 2 == 0) && $this->HTMLFooterE) || ($this->mirrorMargins && ($this->page % 2 == 1) && $this->HTMLFooter) || (!$this->mirrorMargins && $this->HTMLFooter)) {
			$this->writeHTMLFooters();
		}

		/* -- WATERMARK -- */
		if (($this->watermarkText) && ($this->showWatermarkText)) {
			$this->watermark($this->watermarkText, $this->watermarkAngle, is_int($this->watermark_size) ? $this->watermark_size : 120, $this->watermarkTextAlpha); // Watermark text
		}
		if (($this->watermarkImage) && ($this->showWatermarkImage)) {
			$this->watermarkImg($this->watermarkImage, $this->watermarkImageAlpha); // Watermark image
		}
		/* -- END WATERMARK -- */
	}

	/* -- HTML-CSS -- */

	/**
	 * Write HTML code to the document
	 *
	 * Also used internally to parse HTML into buffers
	 *
	 * @param string $html
	 * @param int    $mode  Use HTMLParserMode constants. Controls what parts of the $html code is parsed.
	 * @param bool   $init  Clears and sets buffers to Top level block etc.
	 * @param bool   $close If false leaves buffers etc. in current state, so that it can continue a block etc.
	 */
	function WriteHTML($html, $mode = HTMLParserMode::DEFAULT_MODE, $init = true, $close = true)
	{
		/* Check $html is an integer, float, string, boolean or class with __toString(), otherwise throw exception */
		if (is_scalar($html) === false) {
			if (!is_object($html) || ! method_exists($html, '__toString')) {
				throw new \Mpdf\MpdfException('WriteHTML() requires $html be an integer, float, string, boolean or an object with the __toString() magic method.');
			}
		}

		// Check the mode is valid
		if (in_array($mode, HTMLParserMode::getAllModes(), true) === false) {
			throw new \Mpdf\MpdfException('WriteHTML() requires $mode to be one of the modes defined in HTMLParserMode');
		}

		/* Cast $html as a string */
		$html = (string) $html;

		// @log Parsing CSS & Headers

		if ($init) {
			$this->headerbuffer = '';
			$this->textbuffer = [];
			$this->fixedPosBlockSave = [];
		}
		if ($mode === HTMLParserMode::HEADER_CSS) {
			$html = '<style> ' . $html . ' </style>';
		} // stylesheet only

		if ($this->allow_charset_conversion) {
			if ($mode === HTMLParserMode::DEFAULT_MODE) {
				$this->ReadCharset($html);
			}
			if ($this->charset_in && $mode !== HTMLParserMode::HTML_HEADER_BUFFER) {
				$success = iconv($this->charset_in, 'UTF-8//TRANSLIT', $html);
				if ($success) {
					$html = $success;
				}
			}
		}

		$html = $this->purify_utf8($html, false);
		if ($init) {
			$this->blklvl = 0;
			$this->lastblocklevelchange = 0;
			$this->blk = [];
			$this->initialiseBlock($this->blk[0]);
			$this->blk[0]['width'] = & $this->pgwidth;
			$this->blk[0]['inner_width'] = & $this->pgwidth;
			$this->blk[0]['blockContext'] = $this->blockContext;
		}

		$zproperties = [];
		if ($mode === HTMLParserMode::DEFAULT_MODE || $mode === HTMLParserMode::HEADER_CSS) {
			$this->ReadMetaTags($html);

			if (preg_match('/<base[^>]*href=["\']([^"\'>]*)["\']/i', $html, $m)) {
				$this->SetBasePath($m[1]);
			}
			$html = $this->cssManager->ReadCSS($html);

			if ($this->autoLangToFont && !$this->usingCoreFont && preg_match('/<html [^>]*lang=[\'\"](.*?)[\'\"]/ism', $html, $m)) {
				$html_lang = $m[1];
			}

			if (preg_match('/<html [^>]*dir=[\'\"]\s*rtl\s*[\'\"]/ism', $html)) {
				$zproperties['DIRECTION'] = 'rtl';
			}

			// allow in-line CSS for body tag to be parsed // Get <body> tag inline CSS
			if (preg_match('/<body([^>]*)>(.*?)<\/body>/ism', $html, $m) || preg_match('/<body([^>]*)>(.*)$/ism', $html, $m)) {
				$html = $m[2];
				// Changed to allow style="background: url('bg.jpg')"
				if (preg_match('/style=[\"](.*?)[\"]/ism', $m[1], $mm) || preg_match('/style=[\'](.*?)[\']/ism', $m[1], $mm)) {
					$zproperties = $this->cssManager->readInlineCSS($mm[1]);
				}
				if (preg_match('/dir=[\'\"]\s*rtl\s*[\'\"]/ism', $m[1])) {
					$zproperties['DIRECTION'] = 'rtl';
				}
				if (isset($html_lang) && $html_lang) {
					$zproperties['LANG'] = $html_lang;
				}
				if ($this->autoLangToFont && !$this->onlyCoreFonts && preg_match('/lang=[\'\"](.*?)[\'\"]/ism', $m[1], $mm)) {
					$zproperties['LANG'] = $mm[1];
				}
			}
		}
		$properties = $this->cssManager->MergeCSS('BLOCK', 'BODY', '');
		if ($zproperties) {
			$properties = $this->cssManager->array_merge_recursive_unique($properties, $zproperties);
		}

		if (isset($properties['DIRECTION']) && $properties['DIRECTION']) {
			$this->cssManager->CSS['BODY']['DIRECTION'] = $properties['DIRECTION'];
		}
		if (!isset($this->cssManager->CSS['BODY']['DIRECTION'])) {
			$this->cssManager->CSS['BODY']['DIRECTION'] = $this->directionality;
		} else {
			$this->SetDirectionality($this->cssManager->CSS['BODY']['DIRECTION']);
		}

		$this->setCSS($properties, '', 'BODY');

		$this->blk[0]['InlineProperties'] = $this->saveInlineProperties();

		if ($mode === HTMLParserMode::HEADER_CSS) {
			return '';
		}
		if (!isset($this->cssManager->CSS['BODY'])) {
			$this->cssManager->CSS['BODY'] = [];
		}

		/* -- BACKGROUNDS -- */
		if (isset($properties['BACKGROUND-GRADIENT'])) {
			$this->bodyBackgroundGradient = $properties['BACKGROUND-GRADIENT'];
		}

		if (isset($properties['BACKGROUND-IMAGE']) && $properties['BACKGROUND-IMAGE']) {
			$ret = $this->SetBackground($properties, $this->pgwidth);
			if ($ret) {
				$this->bodyBackgroundImage = $ret;
			}
		}
		/* -- END BACKGROUNDS -- */

		/* -- CSS-PAGE -- */
		// If page-box is set
		if ($this->state == 0 && ((isset($this->cssManager->CSS['@PAGE']) && $this->cssManager->CSS['@PAGE']) || (isset($this->cssManager->CSS['@PAGE>>PSEUDO>>FIRST']) && $this->cssManager->CSS['@PAGE>>PSEUDO>>FIRST']))) { // mPDF 5.7.3
			$this->page_box['current'] = '';
			$this->page_box['using'] = true;
			list($pborientation, $pbmgl, $pbmgr, $pbmgt, $pbmgb, $pbmgh, $pbmgf, $hname, $fname, $bg, $resetpagenum, $pagenumstyle, $suppress, $marks, $newformat) = $this->SetPagedMediaCSS('', false, 'O');
			$this->DefOrientation = $this->CurOrientation = $pborientation;
			$this->orig_lMargin = $this->DeflMargin = $pbmgl;
			$this->orig_rMargin = $this->DefrMargin = $pbmgr;
			$this->orig_tMargin = $this->tMargin = $pbmgt;
			$this->orig_bMargin = $this->bMargin = $pbmgb;
			$this->orig_hMargin = $this->margin_header = $pbmgh;
			$this->orig_fMargin = $this->margin_footer = $pbmgf;
			list($pborientation, $pbmgl, $pbmgr, $pbmgt, $pbmgb, $pbmgh, $pbmgf, $hname, $fname, $bg, $resetpagenum, $pagenumstyle, $suppress, $marks, $newformat) = $this->SetPagedMediaCSS('', true, 'O'); // first page
			$this->show_marks = $marks;
			if ($hname) {
				$this->firstPageBoxHeader = $hname;
			}
			if ($fname) {
				$this->firstPageBoxFooter = $fname;
			}
		}
		/* -- END CSS-PAGE -- */

		$parseonly = false;
		$this->bufferoutput = false;
		if ($mode == HTMLParserMode::HTML_PARSE_NO_WRITE) {
			$parseonly = true;
			// Close any open block tags
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
			}
			// Output any text left in buffer
			if (count($this->textbuffer)) {
				$this->printbuffer($this->textbuffer);
			}
			$this->textbuffer = [];
		} elseif ($mode === HTMLParserMode::HTML_HEADER_BUFFER) {
			// Close any open block tags
			$arr = [];
			$ai = 0;
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $arr, $ai);
			}
			// Output any text left in buffer
			if (count($this->textbuffer)) {
				$this->printbuffer($this->textbuffer);
			}
			$this->bufferoutput = true;
			$this->textbuffer = [];
			$this->headerbuffer = '';
			$properties = $this->cssManager->MergeCSS('BLOCK', 'BODY', '');
			$this->setCSS($properties, '', 'BODY');
		}

		mb_internal_encoding('UTF-8');

		$html = $this->AdjustHTML($html, $this->tabSpaces); // Try to make HTML look more like XHTML

		if ($this->autoScriptToLang) {
			$html = $this->markScriptToLang($html);
		}

		preg_match_all('/<htmlpageheader([^>]*)>(.*?)<\/htmlpageheader>/si', $html, $h);
		for ($i = 0; $i < count($h[1]); $i++) {
			if (preg_match('/name=[\'|\"](.*?)[\'|\"]/', $h[1][$i], $n)) {
				$this->pageHTMLheaders[$n[1]]['html'] = $h[2][$i];
				$this->pageHTMLheaders[$n[1]]['h'] = $this->_getHtmlHeight($h[2][$i]);
			}
		}
		preg_match_all('/<htmlpagefooter([^>]*)>(.*?)<\/htmlpagefooter>/si', $html, $f);
		for ($i = 0; $i < count($f[1]); $i++) {
			if (preg_match('/name=[\'|\"](.*?)[\'|\"]/', $f[1][$i], $n)) {
				$this->pageHTMLfooters[$n[1]]['html'] = $f[2][$i];
				$this->pageHTMLfooters[$n[1]]['h'] = $this->_getHtmlHeight($f[2][$i]);
			}
		}

		$html = preg_replace('/<htmlpageheader.*?<\/htmlpageheader>/si', '', $html);
		$html = preg_replace('/<htmlpagefooter.*?<\/htmlpagefooter>/si', '', $html);

		if ($this->state == 0 && ($mode === HTMLParserMode::DEFAULT_MODE || $mode === HTMLParserMode::HTML_BODY)) {
			$this->AddPage($this->CurOrientation);
		}


		if (isset($hname) && preg_match('/^html_(.*)$/i', $hname, $n)) {
			$this->SetHTMLHeader($this->pageHTMLheaders[$n[1]], 'O', true);
		}
		if (isset($fname) && preg_match('/^html_(.*)$/i', $fname, $n)) {
			$this->SetHTMLFooter($this->pageHTMLfooters[$n[1]], 'O');
		}



		$html = str_replace('<?', '< ', $html); // Fix '<?XML' bug from HTML code generated by MS Word

		$this->checkSIP = false;
		$this->checkSMP = false;
		$this->checkCJK = false;
		if ($this->onlyCoreFonts) {
			$html = $this->SubstituteChars($html);
		} else {
			if (preg_match("/([" . $this->pregRTLchars . "])/u", $html)) {
				$this->biDirectional = true;
			} // *OTL*
			if (preg_match("/([\x{20000}-\x{2FFFF}])/u", $html)) {
				$this->checkSIP = true;
			}
			if (preg_match("/([\x{10000}-\x{1FFFF}])/u", $html)) {
				$this->checkSMP = true;
			}
			/* -- CJK-FONTS -- */
			if (preg_match("/([" . $this->pregCJKchars . "])/u", $html)) {
				$this->checkCJK = true;
			}
			/* -- END CJK-FONTS -- */
		}

		// Don't allow non-breaking spaces that are converted to substituted chars or will break anyway and mess up table width calc.
		$html = str_replace('<tta>160</tta>', chr(32), $html);
		$html = str_replace('</tta><tta>', '|', $html);
		$html = str_replace('</tts><tts>', '|', $html);
		$html = str_replace('</ttz><ttz>', '|', $html);

		// Add new supported tags in the DisableTags function
		$html = strip_tags($html, $this->enabledtags); // remove all unsupported tags, but the ones inside the 'enabledtags' string
		// Explode the string in order to parse the HTML code
		$a = preg_split('/<(.*?)>/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
		// ? more accurate regexp that allows e.g. <a name="Silly <name>">
		// if changing - also change in fn.SubstituteChars()
		// $a = preg_split ('/<((?:[^<>]+(?:"[^"]*"|\'[^\']*\')?)+)>/ms', $html, -1, PREG_SPLIT_DELIM_CAPTURE);

		if ($this->mb_enc) {
			mb_internal_encoding($this->mb_enc);
		}
		$pbc = 0;
		$this->subPos = -1;
		$cnt = count($a);
		for ($i = 0; $i < $cnt; $i++) {
			$e = $a[$i];
			if ($i % 2 == 0) {
				// TEXT
				if ($this->blk[$this->blklvl]['hide']) {
					continue;
				}
				if ($this->inlineDisplayOff) {
					continue;
				}
				if ($this->inMeter) {
					continue;
				}

				if ($this->inFixedPosBlock) {
					$this->fixedPosBlock .= $e;
					continue;
				} // *CSS-POSITION*
				if (strlen($e) == 0) {
					continue;
				}

				if ($this->ignorefollowingspaces && !$this->ispre) {
					if (strlen(ltrim($e)) == 0) {
						continue;
					}
					if ($this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats' && substr($e, 0, 1) == ' ') {
						$this->ignorefollowingspaces = false;
						$e = ltrim($e);
					}
				}

				$this->OTLdata = null;  // mPDF 5.7.1

				$e = UtfString::strcode2utf($e);
				$e = $this->lesser_entity_decode($e);

				if ($this->usingCoreFont) {
					// If core font is selected in document which is not onlyCoreFonts - substitute with non-core font
					if ($this->useSubstitutions && !$this->onlyCoreFonts && $this->subPos < $i && !$this->specialcontent) {
						$cnt += $this->SubstituteCharsNonCore($a, $i, $e);
					}
					// CONVERT ENCODING
					$e = mb_convert_encoding($e, $this->mb_enc, 'UTF-8');
					if ($this->textvar & TextVars::FT_UPPERCASE) {
						$e = mb_strtoupper($e, $this->mb_enc);
					} // mPDF 5.7.1
					elseif ($this->textvar & TextVars::FT_LOWERCASE) {
						$e = mb_strtolower($e, $this->mb_enc);
					} // mPDF 5.7.1
					elseif ($this->textvar & TextVars::FT_CAPITALIZE) {
						$e = mb_convert_case($e, MB_CASE_TITLE, "UTF-8");
					} // mPDF 5.7.1
				} else {
					if ($this->checkSIP && $this->CurrentFont['sipext'] && $this->subPos < $i && (!$this->specialcontent || !$this->useActiveForms)) {
						$cnt += $this->SubstituteCharsSIP($a, $i, $e);
					}

					if ($this->useSubstitutions && !$this->onlyCoreFonts && $this->CurrentFont['type'] != 'Type0' && $this->subPos < $i && (!$this->specialcontent || !$this->useActiveForms)) {
						$cnt += $this->SubstituteCharsMB($a, $i, $e);
					}

					if ($this->textvar & TextVars::FT_UPPERCASE) {
						$e = mb_strtoupper($e, $this->mb_enc);
					} elseif ($this->textvar & TextVars::FT_LOWERCASE) {
						$e = mb_strtolower($e, $this->mb_enc);
					} elseif ($this->textvar & TextVars::FT_CAPITALIZE) {
						$e = mb_convert_case($e, MB_CASE_TITLE, "UTF-8");
					}

					/* -- OTL -- */
					// Use OTL OpenType Table Layout - GSUB & GPOS
					if (isset($this->CurrentFont['useOTL']) && $this->CurrentFont['useOTL'] && (!$this->specialcontent || !$this->useActiveForms)) {
						if (!$this->otl) {
							$this->otl = new Otl($this, $this->fontCache);
						}
						$e = $this->otl->applyOTL($e, $this->CurrentFont['useOTL']);
						$this->OTLdata = $this->otl->OTLdata;
						$this->otl->removeChar($e, $this->OTLdata, "\xef\xbb\xbf"); // Remove ZWNBSP (also Byte order mark FEFF)
					} /* -- END OTL -- */
					else {
						// removes U+200E/U+200F LTR and RTL mark and U+200C/U+200D Zero-width Joiner and Non-joiner
						$e = preg_replace("/[\xe2\x80\x8c\xe2\x80\x8d\xe2\x80\x8e\xe2\x80\x8f]/u", '', $e);
						$e = preg_replace("/[\xef\xbb\xbf]/u", '', $e); // Remove ZWNBSP (also Byte order mark FEFF)
					}
				}

				if (($this->tts) || ($this->ttz) || ($this->tta)) {
					$es = explode('|', $e);
					$e = '';
					foreach ($es as $val) {
						$e .= chr($val);
					}
				}

				//  FORM ELEMENTS
				if ($this->specialcontent) {
					/* -- FORMS -- */
					// SELECT tag (form element)
					if ($this->specialcontent == "type=select") {
						$e = ltrim($e);
						if (!empty($this->OTLdata)) {
							$this->otl->trimOTLdata($this->OTLdata, true, false);
						} // *OTL*
						$stringwidth = $this->GetStringWidth($e);
						if (!isset($this->selectoption['MAXWIDTH']) || $stringwidth > $this->selectoption['MAXWIDTH']) {
							$this->selectoption['MAXWIDTH'] = $stringwidth;
						}
						if (!isset($this->selectoption['SELECTED']) || $this->selectoption['SELECTED'] == '') {
							$this->selectoption['SELECTED'] = $e;
							if (!empty($this->OTLdata)) {
								$this->selectoption['SELECTED-OTLDATA'] = $this->OTLdata;
							} // *OTL*
						}
						// Active Forms
						if (isset($this->selectoption['ACTIVE']) && $this->selectoption['ACTIVE']) {
							$this->selectoption['ITEMS'][] = ['exportValue' => $this->selectoption['currentVAL'], 'content' => $e, 'selected' => $this->selectoption['currentSEL']];
						}
						$this->OTLdata = [];
					} // TEXTAREA
					else {
						$objattr = unserialize($this->specialcontent);
						$objattr['text'] = $e;
						$objattr['OTLdata'] = $this->OTLdata;
						$this->OTLdata = [];
						$te = Mpdf::OBJECT_IDENTIFIER . "type=textarea,objattr=" . serialize($objattr) . Mpdf::OBJECT_IDENTIFIER;
						if ($this->tdbegin) {
							$this->_saveCellTextBuffer($te, $this->HREF);
						} else {
							$this->_saveTextBuffer($te, $this->HREF);
						}
					}
					/* -- END FORMS -- */
				} // TABLE
				elseif ($this->tableLevel) {
					/* -- TABLES -- */
					if ($this->tdbegin) {
						if (($this->ignorefollowingspaces) && !$this->ispre) {
							$e = ltrim($e);
							if (!empty($this->OTLdata)) {
								$this->otl->trimOTLdata($this->OTLdata, true, false);
							} // *OTL*
						}
						if ($e || $e === '0') {
							if ($this->blockjustfinished && $this->cell[$this->row][$this->col]['s'] > 0) {
								$this->_saveCellTextBuffer("\n");
								if (!isset($this->cell[$this->row][$this->col]['maxs'])) {
									$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
								} elseif ($this->cell[$this->row][$this->col]['maxs'] < $this->cell[$this->row][$this->col]['s']) {
									$this->cell[$this->row][$this->col]['maxs'] = $this->cell[$this->row][$this->col]['s'];
								}
								$this->cell[$this->row][$this->col]['s'] = 0; // reset
							}
							$this->blockjustfinished = false;

							if (!isset($this->cell[$this->row][$this->col]['R']) || !$this->cell[$this->row][$this->col]['R']) {
								if (isset($this->cell[$this->row][$this->col]['s'])) {
									$this->cell[$this->row][$this->col]['s'] += $this->GetStringWidth($e, false, $this->OTLdata, $this->textvar);
								} else {
									$this->cell[$this->row][$this->col]['s'] = $this->GetStringWidth($e, false, $this->OTLdata, $this->textvar);
								}
								if (!empty($this->spanborddet)) {
									$this->cell[$this->row][$this->col]['s'] += (isset($this->spanborddet['L']['w']) ? $this->spanborddet['L']['w'] : 0) + (isset($this->spanborddet['R']['w']) ? $this->spanborddet['R']['w'] : 0);
								}
							}

							$this->_saveCellTextBuffer($e, $this->HREF);

							if (substr($this->cell[$this->row][$this->col]['a'], 0, 1) == 'D') {

								$dp = $this->decimal_align[substr($this->cell[$this->row][$this->col]['a'], 0, 2)];
								$s = preg_split('/' . preg_quote($dp, '/') . '/', $e, 2);  // ? needs to be /u if not core
								$s0 = $this->GetStringWidth($s[0], false);

								if (isset($s[1]) && $s[1]) {
									$s1 = $this->GetStringWidth(($s[1] . $dp), false);
								} else {
									$s1 = 0;
								}

								if (!isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'])) {
									if ($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'] === false) {
										$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'] = [];
									}
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'] = $s0;
								} else {
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0'] = max($s0, $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs0']);
								}

								if (!isset($this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'])) {
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'] = $s1;
								} else {
									$this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1'] = max($s1, $this->table[$this->tableLevel][$this->tbctr[$this->tableLevel]]['decimal_align'][$this->col]['maxs1']);
								}
							}

							$this->nestedtablejustfinished = false;
							$this->linebreakjustfinished = false;
						}
					}
					/* -- END TABLES -- */
				} // ALL ELSE
				else {
					if ($this->ignorefollowingspaces && !$this->ispre) {
						$e = ltrim($e);
						if (!empty($this->OTLdata)) {
							$this->otl->trimOTLdata($this->OTLdata, true, false);
						} // *OTL*
					}
					if ($e || $e === '0') {
						$this->_saveTextBuffer($e, $this->HREF);
					}
				}
				if ($e || $e === '0') {
					$this->ignorefollowingspaces = false; // mPDF 6
				}
				if (substr($e, -1, 1) == ' ' && !$this->ispre && $this->FontFamily != 'csymbol' && $this->FontFamily != 'czapfdingbats') {
					$this->ignorefollowingspaces = true;
				}
			} else { // TAG **
				if (isset($e[0]) && $e[0] == '/') {
					$endtag = trim(strtoupper(substr($e, 1)));

					/* -- CSS-POSITION -- */
					// mPDF 6
					if ($this->inFixedPosBlock) {
						if (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags)) {
							$this->fixedPosBlockDepth--;
						}
						if ($this->fixedPosBlockDepth == 0) {
							$this->fixedPosBlockSave[] = [$this->fixedPosBlock, $this->fixedPosBlockBBox, $this->page];
							$this->fixedPosBlock = '';
							$this->inFixedPosBlock = false;
							continue;
						}
						$this->fixedPosBlock .= '<' . $e . '>';
						continue;
					}
					/* -- END CSS-POSITION -- */

					// mPDF 6
					// Correct for tags where HTML5 specifies optional end tags (see also OpenTag() )
					if ($this->allow_html_optional_endtags && !$parseonly) {
						if (isset($this->blk[$this->blklvl]['tag'])) {
							$closed = false;
							// li end tag may be omitted if there is no more content in the parent element
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'LI' && $endtag != 'LI' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('LI', $a, $i);
								$closed = true;
							}
							// dd end tag may be omitted if there is no more content in the parent element
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'DD' && $endtag != 'DD' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('DD', $a, $i);
								$closed = true;
							}
							// p end tag may be omitted if there is no more content in the parent element and the parent element is not an A element [??????]
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'P' && $endtag != 'P' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('P', $a, $i);
								$closed = true;
							}
							// option end tag may be omitted if there is no more content in the parent element
							if (!$closed && $this->blk[$this->blklvl]['tag'] == 'OPTION' && $endtag != 'OPTION' && (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags))) {
								$this->tag->CloseTag('OPTION', $a, $i);
								$closed = true;
							}
						}
						/* -- TABLES -- */
						// Check for Table tags where HTML specifies optional end tags,
						if ($endtag == 'TABLE') {
							if ($this->lastoptionaltag == 'THEAD' || $this->lastoptionaltag == 'TBODY' || $this->lastoptionaltag == 'TFOOT') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
							}
							if ($this->lastoptionaltag == 'TR') {
								$this->tag->CloseTag('TR', $a, $i);
							}
							if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
								$this->tag->CloseTag('TR', $a, $i);
							}
						}
						if ($endtag == 'THEAD' || $endtag == 'TBODY' || $endtag == 'TFOOT') {
							if ($this->lastoptionaltag == 'TR') {
								$this->tag->CloseTag('TR', $a, $i);
							}
							if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
								$this->tag->CloseTag('TR', $a, $i);
							}
						}
						if ($endtag == 'TR') {
							if ($this->lastoptionaltag == 'TD' || $this->lastoptionaltag == 'TH') {
								$this->tag->CloseTag($this->lastoptionaltag, $a, $i);
							}
						}
						/* -- END TABLES -- */
					}


					// mPDF 6
					if ($this->blk[$this->blklvl]['hide']) {
						if (in_array($endtag, $this->outerblocktags) || in_array($endtag, $this->innerblocktags)) {
							unset($this->blk[$this->blklvl]);
							$this->blklvl--;
						}
						continue;
					}

					// mPDF 6
					$this->tag->CloseTag($endtag, $a, $i); // mPDF 6
				} else { // OPENING TAG
					if ($this->blk[$this->blklvl]['hide']) {
						if (strpos($e, ' ')) {
							$te = strtoupper(substr($e, 0, strpos($e, ' ')));
						} else {
							$te = strtoupper($e);
						}
						// mPDF 6
						if ($te == 'THEAD' || $te == 'TBODY' || $te == 'TFOOT' || $te == 'TR' || $te == 'TD' || $te == 'TH') {
							$this->lastoptionaltag = $te;
						}
						if (in_array($te, $this->outerblocktags) || in_array($te, $this->innerblocktags)) {
							$this->blklvl++;
							$this->blk[$this->blklvl]['hide'] = true;
							$this->blk[$this->blklvl]['tag'] = $te; // mPDF 6
						}
						continue;
					}

					/* -- CSS-POSITION -- */
					if ($this->inFixedPosBlock) {
						if (strpos($e, ' ')) {
							$te = strtoupper(substr($e, 0, strpos($e, ' ')));
						} else {
							$te = strtoupper($e);
						}
						$this->fixedPosBlock .= '<' . $e . '>';
						if (in_array($te, $this->outerblocktags) || in_array($te, $this->innerblocktags)) {
							$this->fixedPosBlockDepth++;
						}
						continue;
					}
					/* -- END CSS-POSITION -- */
					$regexp = '|=\'(.*?)\'|s'; // eliminate single quotes, if any
					$e = preg_replace($regexp, "=\"\$1\"", $e);
					// changes anykey=anyvalue to anykey="anyvalue" (only do this inside [some] tags)
					if (substr($e, 0, 10) != 'pageheader' && substr($e, 0, 10) != 'pagefooter' && substr($e, 0, 12) != 'tocpagebreak' && substr($e, 0, 10) != 'indexentry' && substr($e, 0, 8) != 'tocentry') { // mPDF 6  (ZZZ99H)
						$regexp = '| (\\w+?)=([^\\s>"]+)|si';
						$e = preg_replace($regexp, " \$1=\"\$2\"", $e);
					}

					$e = preg_replace('/ (\\S+?)\s*=\s*"/i', " \\1=\"", $e);

					// Fix path values, if needed
					$orig_srcpath = '';
					if ((stristr($e, "href=") !== false) or ( stristr($e, "src=") !== false)) {
						$regexp = '/ (href|src)\s*=\s*"(.*?)"/i';
						preg_match($regexp, $e, $auxiliararray);
						if (isset($auxiliararray[2])) {
							$path = $auxiliararray[2];
						} else {
							$path = '';
						}
						if (trim($path) != '' && !(stristr($e, "src=") !== false && substr($path, 0, 4) == 'var:') && substr($path, 0, 1) != '@') {
							$path = htmlspecialchars_decode($path); // mPDF 5.7.4 URLs
							$orig_srcpath = $path;
							$this->GetFullPath($path);
							$regexp = '/ (href|src)="(.*?)"/i';
							$e = preg_replace($regexp, ' \\1="' . $path . '"', $e);
						}
					}//END of Fix path values
					// Extract attributes
					$contents = [];
					$contents1 = [];
					$contents2 = [];
					// Changed to allow style="background: url('bg.jpg')"
					// Changed to improve performance; maximum length of \S (attribute) = 16
					// Increase allowed attribute name to 32 - cutting off "toc-even-header-name" etc.
					preg_match_all('/\\S{1,32}=["][^"]*["]/', $e, $contents1);
					preg_match_all('/\\S{1,32}=[\'][^\']*[\']/i', $e, $contents2);

					$contents = array_merge($contents1, $contents2);
					preg_match('/\\S+/', $e, $a2);
					$tag = (isset($a2[0]) ? strtoupper($a2[0]) : '');
					$attr = [];
					if ($orig_srcpath) {
						$attr['ORIG_SRC'] = $orig_srcpath;
					}
					if (!empty($contents)) {
						foreach ($contents[0] as $v) {
							// Changed to allow style="background: url('bg.jpg')"
							if (preg_match('/^([^=]*)=["]?([^"]*)["]?$/', $v, $a3) || preg_match('/^([^=]*)=[\']?([^\']*)[\']?$/', $v, $a3)) {
								if (strtoupper($a3[1]) == 'ID' || strtoupper($a3[1]) == 'CLASS') { // 4.2.013 Omits STYLE
									$attr[strtoupper($a3[1])] = trim(strtoupper($a3[2]));
								} // includes header-style-right etc. used for <pageheader>
								elseif (preg_match('/^(HEADER|FOOTER)-STYLE/i', $a3[1])) {
									$attr[strtoupper($a3[1])] = trim(strtoupper($a3[2]));
								} else {
									$attr[strtoupper($a3[1])] = trim($a3[2]);
								}
							}
						}
					}
					$this->tag->OpenTag($tag, $attr, $a, $i); // mPDF 6
					/* -- CSS-POSITION -- */
					if ($this->inFixedPosBlock) {
						$this->fixedPosBlockBBox = [$tag, $attr, $this->x, $this->y];
						$this->fixedPosBlock = '';
						$this->fixedPosBlockDepth = 1;
					}
					/* -- END CSS-POSITION -- */
					if (preg_match('/\/$/', $e)) {
						$this->tag->CloseTag($tag, $a, $i);
					}
				}
			} // end TAG
		} // end of	foreach($a as $i=>$e)

		if ($close) {
			// Close any open block tags
			for ($b = $this->blklvl; $b > 0; $b--) {
				$this->tag->CloseTag($this->blk[$b]['tag'], $a, $i);
			}

			// Output any text left in buffer
			if (count($this->textbuffer) && !$parseonly) {
				$this->printbuffer($this->textbuffer);
			}
			if (!$parseonly) {
				$this->textbuffer = [];
			}

			/* -- CSS-FLOAT -- */
			// If ended with a float, need to move to end page
			$currpos = $this->page * 1000 + $this->y;
			if (isset($this->blk[$this->blklvl]['float_endpos']) && $this->blk[$this->blklvl]['float_endpos'] > $currpos) {
				$old_page = $this->page;
				$new_page = intval($this->blk[$this->blklvl]['float_endpos'] / 1000);
				if ($old_page != $new_page) {
					$s = $this->PrintPageBackgrounds();
					// Writes after the marker so not overwritten later by page background etc.
					$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->pages[$this->page]);
					$this->pageBackgrounds = [];
					$this->page = $new_page;
					$this->ResetMargins();
					$this->Reset();
					$this->pageoutput[$this->page] = [];
				}
				$this->y = (round($this->blk[$this->blklvl]['float_endpos'] * 1000) % 1000000) / 1000; // mod changes operands to integers before processing
			}
			/* -- END CSS-FLOAT -- */

			/* -- CSS-IMAGE-FLOAT -- */
			$this->printfloatbuffer();
			/* -- END CSS-IMAGE-FLOAT -- */

			// Create Internal Links, if needed
			if (!empty($this->internallink)) {

				foreach ($this->internallink as $k => $v) {

					if (strpos($k, "#") !== false) {
						continue;
					}

					if (!is_array($v)) {
						continue;
					}

					$ypos = $v['Y'];
					$pagenum = $v['PAGE'];
					$sharp = "#";

					while (array_key_exists($sharp . $k, $this->internallink)) {
						$internallink = $this->internallink[$sharp . $k];
						$this->SetLink($internallink, $ypos, $pagenum);
						$sharp .= "#";
					}
				}
			}

			$this->bufferoutput = false;

			/* -- CSS-POSITION -- */
			if (count($this->fixedPosBlockSave)) {
				foreach ($this->fixedPosBlockSave as $fpbs) {
					$old_page = $this->page;
					$this->page = $fpbs[2];
					$this->WriteFixedPosHTML($fpbs[0], 0, 0, 100, 100, 'auto', $fpbs[1]);  // 0,0,10,10 are overwritten by bbox
					$this->page = $old_page;
				}
				$this->fixedPosBlockSave = [];
			}
			/* -- END CSS-POSITION -- */
		}
	}

	/* -- CSS-POSITION -- */

	function WriteFixedPosHTML($html, $x, $y, $w, $h, $overflow = 'visible', $bounding = [])
	{
		// $overflow can be 'hidden', 'visible' or 'auto' - 'auto' causes autofit to size
		// Annotations disabled - enabled in mPDF 5.0
		// Links do work
		// Will always go on current page (or start Page 1 if required)
		// Probably INCOMPATIBLE WITH keep with table, columns etc.
		// Called externally or interally via <div style="position: [fixed|absolute]">
		// When used internally, $x $y $w $h and $overflow are all overridden by $bounding

		$overflow = strtolower($overflow);
		if ($this->state == 0) {
			$this->AddPage($this->CurOrientation);
		}
		$save_y = $this->y;
		$save_x = $this->x;
		$this->fullImageHeight = $this->h;
		$save_cols = false;
		/* -- COLUMNS -- */
		if ($this->ColActive) {
			$save_cols = true;
			$save_nbcol = $this->NbCol; // other values of gap and vAlign will not change by setting Columns off
			$this->SetColumns(0);
		}
		/* -- END COLUMNS -- */
		$save_annots = $this->title2annots; // *ANNOTATIONS*
		$this->writingHTMLheader = true; // a FIX to stop pagebreaks etc.
		$this->writingHTMLfooter = true;
		$this->InFooter = true; // suppresses autopagebreaks
		$save_bgs = $this->pageBackgrounds;
		$checkinnerhtml = preg_replace('/\s/', '', $html);
		$rotate = 0;

		if ($w > $this->w) {
			$x = 0;
			$w = $this->w;
		}
		if ($h > $this->h) {
			$y = 0;
			$h = $this->h;
		}
		if ($x > $this->w) {
			$x = $this->w - $w;
		}
		if ($y > $this->h) {
			$y = $this->h - $h;
		}

		if (!empty($bounding)) {
			// $cont_ containing block = full physical page (position: absolute) or page inside margins (position: fixed)
			// $bbox_ Bounding box is the <div> which is positioned absolutely/fixed
			// top/left/right/bottom/width/height/background*/border*/padding*/margin* are taken from bounding
			// font*[family/size/style/weight]/line-height/text*[align/decoration/transform/indent]/color are transferred to $inner
			// as an enclosing <div> (after having checked ID/CLASS)
			// $x, $y, $w, $h are inside of $bbox_ = containing box for $inner_
			// $inner_ InnerHTML is the contents of that block to be output
			$tag = $bounding[0];
			$attr = $bounding[1];
			$orig_x0 = $bounding[2];
			$orig_y0 = $bounding[3];

			// As in WriteHTML() initialising
			$this->blklvl = 0;
			$this->lastblocklevelchange = 0;
			$this->blk = [];
			$this->initialiseBlock($this->blk[0]);

			$this->blk[0]['width'] = & $this->pgwidth;
			$this->blk[0]['inner_width'] = & $this->pgwidth;

			$this->blk[0]['blockContext'] = $this->blockContext;

			$properties = $this->cssManager->MergeCSS('BLOCK', 'BODY', '');
			$this->setCSS($properties, '', 'BODY');
			$this->blklvl = 1;
			$this->initialiseBlock($this->blk[1]);
			$this->blk[1]['tag'] = $tag;
			$this->blk[1]['attr'] = $attr;
			$this->Reset();
			$p = $this->cssManager->MergeCSS('BLOCK', $tag, $attr);
			if (isset($p['ROTATE']) && ($p['ROTATE'] == 90 || $p['ROTATE'] == -90 || $p['ROTATE'] == 180)) {
				$rotate = $p['ROTATE'];
			} // mPDF 6
			if (isset($p['OVERFLOW'])) {
				$overflow = strtolower($p['OVERFLOW']);
			}
			if (strtolower($p['POSITION']) == 'fixed') {
				$cont_w = $this->pgwidth; // $this->blk[0]['inner_width'];
				$cont_h = $this->h - $this->tMargin - $this->bMargin;
				$cont_x = $this->lMargin;
				$cont_y = $this->tMargin;
			} else {
				$cont_w = $this->w; // ABSOLUTE;
				$cont_h = $this->h;
				$cont_x = 0;
				$cont_y = 0;
			}

			// Pass on in-line properties to the innerhtml
			$css = '';
			if (isset($p['TEXT-ALIGN'])) {
				$css .= 'text-align: ' . strtolower($p['TEXT-ALIGN']) . '; ';
			}
			if (isset($p['TEXT-TRANSFORM'])) {
				$css .= 'text-transform: ' . strtolower($p['TEXT-TRANSFORM']) . '; ';
			}
			if (isset($p['TEXT-INDENT'])) {
				$css .= 'text-indent: ' . strtolower($p['TEXT-INDENT']) . '; ';
			}
			if (isset($p['TEXT-DECORATION'])) {
				$css .= 'text-decoration: ' . strtolower($p['TEXT-DECORATION']) . '; ';
			}
			if (isset($p['FONT-FAMILY'])) {
				$css .= 'font-family: ' . strtolower($p['FONT-FAMILY']) . '; ';
			}
			if (isset($p['FONT-STYLE'])) {
				$css .= 'font-style: ' . strtolower($p['FONT-STYLE']) . '; ';
			}
			if (isset($p['FONT-WEIGHT'])) {
				$css .= 'font-weight: ' . strtolower($p['FONT-WEIGHT']) . '; ';
			}
			if (isset($p['FONT-SIZE'])) {
				$css .= 'font-size: ' . strtolower($p['FONT-SIZE']) . '; ';
			}
			if (isset($p['LINE-HEIGHT'])) {
				$css .= 'line-height: ' . strtolower($p['LINE-HEIGHT']) . '; ';
			}
			if (isset($p['TEXT-SHADOW'])) {
				$css .= 'text-shadow: ' . strtolower($p['TEXT-SHADOW']) . '; ';
			}
			if (isset($p['LETTER-SPACING'])) {
				$css .= 'letter-spacing: ' . strtolower($p['LETTER-SPACING']) . '; ';
			}
			// mPDF 6
			if (isset($p['FONT-VARIANT-POSITION'])) {
				$css .= 'font-variant-position: ' . strtolower($p['FONT-VARIANT-POSITION']) . '; ';
			}
			if (isset($p['FONT-VARIANT-CAPS'])) {
				$css .= 'font-variant-caps: ' . strtolower($p['FONT-VARIANT-CAPS']) . '; ';
			}
			if (isset($p['FONT-VARIANT-LIGATURES'])) {
				$css .= 'font-variant-ligatures: ' . strtolower($p['FONT-VARIANT-LIGATURES']) . '; ';
			}
			if (isset($p['FONT-VARIANT-NUMERIC'])) {
				$css .= 'font-variant-numeric: ' . strtolower($p['FONT-VARIANT-NUMERIC']) . '; ';
			}
			if (isset($p['FONT-VARIANT-ALTERNATES'])) {
				$css .= 'font-variant-alternates: ' . strtolower($p['FONT-VARIANT-ALTERNATES']) . '; ';
			}
			if (isset($p['FONT-FEATURE-SETTINGS'])) {
				$css .= 'font-feature-settings: ' . strtolower($p['FONT-FEATURE-SETTINGS']) . '; ';
			}
			if (isset($p['FONT-LANGUAGE-OVERRIDE'])) {
				$css .= 'font-language-override: ' . strtolower($p['FONT-LANGUAGE-OVERRIDE']) . '; ';
			}
			if (isset($p['FONT-KERNING'])) {
				$css .= 'font-kerning: ' . strtolower($p['FONT-KERNING']) . '; ';
			}

			if (isset($p['COLOR'])) {
				$css .= 'color: ' . strtolower($p['COLOR']) . '; ';
			}
			if (isset($p['Z-INDEX'])) {
				$css .= 'z-index: ' . $p['Z-INDEX'] . '; ';
			}
			if ($css) {
				$html = '<div style="' . $css . '">' . $html . '</div>';
			}
			// Copy over (only) the properties to set for border and background
			$pb = [];
			$pb['MARGIN-TOP'] = (isset($p['MARGIN-TOP']) ? $p['MARGIN-TOP'] : '');
			$pb['MARGIN-RIGHT'] = (isset($p['MARGIN-RIGHT']) ? $p['MARGIN-RIGHT'] : '');
			$pb['MARGIN-BOTTOM'] = (isset($p['MARGIN-BOTTOM']) ? $p['MARGIN-BOTTOM'] : '');
			$pb['MARGIN-LEFT'] = (isset($p['MARGIN-LEFT']) ? $p['MARGIN-LEFT'] : '');
			$pb['PADDING-TOP'] = (isset($p['PADDING-TOP']) ? $p['PADDING-TOP'] : '');
			$pb['PADDING-RIGHT'] = (isset($p['PADDING-RIGHT']) ? $p['PADDING-RIGHT'] : '');
			$pb['PADDING-BOTTOM'] = (isset($p['PADDING-BOTTOM']) ? $p['PADDING-BOTTOM'] : '');
			$pb['PADDING-LEFT'] = (isset($p['PADDING-LEFT']) ? $p['PADDING-LEFT'] : '');
			$pb['BORDER-TOP'] = (isset($p['BORDER-TOP']) ? $p['BORDER-TOP'] : '');
			$pb['BORDER-RIGHT'] = (isset($p['BORDER-RIGHT']) ? $p['BORDER-RIGHT'] : '');
			$pb['BORDER-BOTTOM'] = (isset($p['BORDER-BOTTOM']) ? $p['BORDER-BOTTOM'] : '');
			$pb['BORDER-LEFT'] = (isset($p['BORDER-LEFT']) ? $p['BORDER-LEFT'] : '');
			if (isset($p['BORDER-TOP-LEFT-RADIUS-H'])) {
				$pb['BORDER-TOP-LEFT-RADIUS-H'] = $p['BORDER-TOP-LEFT-RADIUS-H'];
			}
			if (isset($p['BORDER-TOP-LEFT-RADIUS-V'])) {
				$pb['BORDER-TOP-LEFT-RADIUS-V'] = $p['BORDER-TOP-LEFT-RADIUS-V'];
			}
			if (isset($p['BORDER-TOP-RIGHT-RADIUS-H'])) {
				$pb['BORDER-TOP-RIGHT-RADIUS-H'] = $p['BORDER-TOP-RIGHT-RADIUS-H'];
			}
			if (isset($p['BORDER-TOP-RIGHT-RADIUS-V'])) {
				$pb['BORDER-TOP-RIGHT-RADIUS-V'] = $p['BORDER-TOP-RIGHT-RADIUS-V'];
			}
			if (isset($p['BORDER-BOTTOM-LEFT-RADIUS-H'])) {
				$pb['BORDER-BOTTOM-LEFT-RADIUS-H'] = $p['BORDER-BOTTOM-LEFT-RADIUS-H'];
			}
			if (isset($p['BORDER-BOTTOM-LEFT-RADIUS-V'])) {
				$pb['BORDER-BOTTOM-LEFT-RADIUS-V'] = $p['BORDER-BOTTOM-LEFT-RADIUS-V'];
			}
			if (isset($p['BORDER-BOTTOM-RIGHT-RADIUS-H'])) {
				$pb['BORDER-BOTTOM-RIGHT-RADIUS-H'] = $p['BORDER-BOTTOM-RIGHT-RADIUS-H'];
			}
			if (isset($p['BORDER-BOTTOM-RIGHT-RADIUS-V'])) {
				$pb['BORDER-BOTTOM-RIGHT-RADIUS-V'] = $p['BORDER-BOTTOM-RIGHT-RADIUS-V'];
			}
			if (isset($p['BACKGROUND-COLOR'])) {
				$pb['BACKGROUND-COLOR'] = $p['BACKGROUND-COLOR'];
			}
			if (isset($p['BOX-SHADOW'])) {
				$pb['BOX-SHADOW'] = $p['BOX-SHADOW'];
			}
			/* -- BACKGROUNDS -- */
			if (isset($p['BACKGROUND-IMAGE'])) {
				$pb['BACKGROUND-IMAGE'] = $p['BACKGROUND-IMAGE'];
			}
			if (isset($p['BACKGROUND-IMAGE-RESIZE'])) {
				$pb['BACKGROUND-IMAGE-RESIZE'] = $p['BACKGROUND-IMAGE-RESIZE'];
			}
			if (isset($p['BACKGROUND-IMAGE-OPACITY'])) {
				$pb['BACKGROUND-IMAGE-OPACITY'] = $p['BACKGROUND-IMAGE-OPACITY'];
			}
			if (isset($p['BACKGROUND-REPEAT'])) {
				$pb['BACKGROUND-REPEAT'] = $p['BACKGROUND-REPEAT'];
			}
			if (isset($p['BACKGROUND-POSITION'])) {
				$pb['BACKGROUND-POSITION'] = $p['BACKGROUND-POSITION'];
			}
			if (isset($p['BACKGROUND-GRADIENT'])) {
				$pb['BACKGROUND-GRADIENT'] = $p['BACKGROUND-GRADIENT'];
			}
			if (isset($p['BACKGROUND-SIZE'])) {
				$pb['BACKGROUND-SIZE'] = $p['BACKGROUND-SIZE'];
			}
			if (isset($p['BACKGROUND-ORIGIN'])) {
				$pb['BACKGROUND-ORIGIN'] = $p['BACKGROUND-ORIGIN'];
			}
			if (isset($p['BACKGROUND-CLIP'])) {
				$pb['BACKGROUND-CLIP'] = $p['BACKGROUND-CLIP'];
			}

			/* -- END BACKGROUNDS -- */

			$this->setCSS($pb, 'BLOCK', $tag);

			// ================================================================
			$bbox_br = $this->blk[1]['border_right']['w'];
			$bbox_bl = $this->blk[1]['border_left']['w'];
			$bbox_bt = $this->blk[1]['border_top']['w'];
			$bbox_bb = $this->blk[1]['border_bottom']['w'];
			$bbox_pr = $this->blk[1]['padding_right'];
			$bbox_pl = $this->blk[1]['padding_left'];
			$bbox_pt = $this->blk[1]['padding_top'];
			$bbox_pb = $this->blk[1]['padding_bottom'];
			$bbox_mr = $this->blk[1]['margin_right'];
			if (isset($p['MARGIN-RIGHT']) && strtolower($p['MARGIN-RIGHT']) == 'auto') {
				$bbox_mr = 'auto';
			}
			$bbox_ml = $this->blk[1]['margin_left'];
			if (isset($p['MARGIN-LEFT']) && strtolower($p['MARGIN-LEFT']) == 'auto') {
				$bbox_ml = 'auto';
			}
			$bbox_mt = $this->blk[1]['margin_top'];
			if (isset($p['MARGIN-TOP']) && strtolower($p['MARGIN-TOP']) == 'auto') {
				$bbox_mt = 'auto';
			}
			$bbox_mb = $this->blk[1]['margin_bottom'];
			if (isset($p['MARGIN-BOTTOM']) && strtolower($p['MARGIN-BOTTOM']) == 'auto') {
				$bbox_mb = 'auto';
			}
			if (isset($p['LEFT']) && strtolower($p['LEFT']) != 'auto') {
				$bbox_left = $this->sizeConverter->convert($p['LEFT'], $cont_w, $this->FontSize, false);
			} else {
				$bbox_left = 'auto';
			}
			if (isset($p['TOP']) && strtolower($p['TOP']) != 'auto') {
				$bbox_top = $this->sizeConverter->convert($p['TOP'], $cont_h, $this->FontSize, false);
			} else {
				$bbox_top = 'auto';
			}
			if (isset($p['RIGHT']) && strtolower($p['RIGHT']) != 'auto') {
				$bbox_right = $this->sizeConverter->convert($p['RIGHT'], $cont_w, $this->FontSize, false);
			} else {
				$bbox_right = 'auto';
			}
			if (isset($p['BOTTOM']) && strtolower($p['BOTTOM']) != 'auto') {
				$bbox_bottom = $this->sizeConverter->convert($p['BOTTOM'], $cont_h, $this->FontSize, false);
			} else {
				$bbox_bottom = 'auto';
			}
			if (isset($p['WIDTH']) && strtolower($p['WIDTH']) != 'auto') {
				$inner_w = $this->sizeConverter->convert($p['WIDTH'], $cont_w, $this->FontSize, false);
			} else {
				$inner_w = 'auto';
			}
			if (isset($p['HEIGHT']) && strtolower($p['HEIGHT']) != 'auto') {
				$inner_h = $this->sizeConverter->convert($p['HEIGHT'], $cont_h, $this->FontSize, false);
			} else {
				$inner_h = 'auto';
			}

			// If bottom or right pos are set and not left / top - save this to adjust rotated block later
			if ($rotate == 90 || $rotate == -90) { // mPDF 6
				if ($bbox_left === 'auto' && $bbox_right !== 'auto') {
					$rot_rpos = $bbox_right;
				} else {
					$rot_rpos = false;
				}
				if ($bbox_top === 'auto' && $bbox_bottom !== 'auto') {
					$rot_bpos = $bbox_bottom;
				} else {
					$rot_bpos = false;
				}
			}

			// ================================================================
			if ($checkinnerhtml == '' && $inner_h === 'auto') {
				$inner_h = 0.0001;
			}
			if ($checkinnerhtml == '' && $inner_w === 'auto') {
				$inner_w = 2 * $this->GetCharWidth('W', false);
			}
			// ================================================================
			// Algorithm from CSS2.1  See http://www.w3.org/TR/CSS21/visudet.html#abs-non-replaced-height
			// mPD 5.3.14
			// Special case (not CSS) if all not specified, centre vertically on page
			$bbox_top_orig = '';
			if ($bbox_top === 'auto' && $inner_h === 'auto' && $bbox_bottom === 'auto' && $bbox_mt === 'auto' && $bbox_mb === 'auto') {
				$bbox_top_orig = $bbox_top;
				if ($bbox_mt === 'auto') {
					$bbox_mt = 0;
				}
				if ($bbox_mb === 'auto') {
					$bbox_mb = 0;
				}
				$bbox_top = $orig_y0 - $bbox_mt - $cont_y;
				// solve for $bbox_bottom when content_h known - $inner_h=='auto' && $bbox_bottom=='auto'
			} // mPD 5.3.14
			elseif ($bbox_top === 'auto' && $inner_h === 'auto' && $bbox_bottom === 'auto') {
				$bbox_top_orig = $bbox_top = $orig_y0 - $cont_y;
				if ($bbox_mt === 'auto') {
					$bbox_mt = 0;
				}
				if ($bbox_mb === 'auto') {
					$bbox_mb = 0;
				}
				// solve for $bbox_bottom when content_h known - $inner_h=='auto' && $bbox_bottom=='auto'
			} elseif ($bbox_top !== 'auto' && $inner_h !== 'auto' && $bbox_bottom !== 'auto') {
				if ($bbox_mt === 'auto' && $bbox_mb === 'auto') {
					$x = $cont_h - $bbox_top - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_bottom;
					$bbox_mt = $bbox_mb = ($x / 2);
				} elseif ($bbox_mt === 'auto') {
					$bbox_mt = $cont_h - $bbox_top - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
				} elseif ($bbox_mb === 'auto') {
					$bbox_mb = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_bottom;
				} else {
					$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt;
				}
			} else {
				if ($bbox_mt === 'auto') {
					$bbox_mt = 0;
				}
				if ($bbox_mb === 'auto') {
					$bbox_mb = 0;
				}
				if ($bbox_top === 'auto' && $inner_h === 'auto' && $bbox_bottom !== 'auto') {
					// solve for $bbox_top when content_h known - $inner_h=='auto' && $bbox_top =='auto'
				} elseif ($bbox_top === 'auto' && $bbox_bottom === 'auto' && $inner_h !== 'auto') {
					$bbox_top = $orig_y0 - $bbox_mt - $cont_y;
					$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt;
				} elseif ($inner_h === 'auto' && $bbox_bottom === 'auto' && $bbox_top !== 'auto') {
					// solve for $bbox_bottom when content_h known - $inner_h=='auto' && $bbox_bottom=='auto'
				} elseif ($bbox_top === 'auto' && $inner_h !== 'auto' && $bbox_bottom !== 'auto') {
					$bbox_top = $cont_h - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt - $bbox_bottom;
				} elseif ($inner_h === 'auto' && $bbox_top !== 'auto' && $bbox_bottom !== 'auto') {
					$inner_h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mt - $bbox_bottom;
				} elseif ($bbox_bottom === 'auto' && $bbox_top !== 'auto' && $inner_h !== 'auto') {
					$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mt;
				}
			}

			// THEN DO SAME FOR WIDTH
			// http://www.w3.org/TR/CSS21/visudet.html#abs-non-replaced-width
			if ($bbox_left === 'auto' && $inner_w === 'auto' && $bbox_right === 'auto') {
				if ($bbox_ml === 'auto') {
					$bbox_ml = 0;
				}
				if ($bbox_mr === 'auto') {
					$bbox_mr = 0;
				}
				// IF containing element RTL, should set $bbox_right
				$bbox_left = $orig_x0 - $bbox_ml - $cont_x;
				// solve for $bbox_right when content_w known - $inner_w=='auto' && $bbox_right=='auto'
			} elseif ($bbox_left !== 'auto' && $inner_w !== 'auto' && $bbox_right !== 'auto') {
				if ($bbox_ml === 'auto' && $bbox_mr === 'auto') {
					$x = $cont_w - $bbox_left - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_right;
					$bbox_ml = $bbox_mr = ($x / 2);
				} elseif ($bbox_ml === 'auto') {
					$bbox_ml = $cont_w - $bbox_left - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_mr - $bbox_right;
				} elseif ($bbox_mr === 'auto') {
					$bbox_mr = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_right;
				} else {
					$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
				}
			} else {
				if ($bbox_ml === 'auto') {
					$bbox_ml = 0;
				}
				if ($bbox_mr === 'auto') {
					$bbox_mr = 0;
				}
				if ($bbox_left === 'auto' && $inner_w === 'auto' && $bbox_right !== 'auto') {
					// solve for $bbox_left when content_w known - $inner_w=='auto' && $bbox_left =='auto'
				} elseif ($bbox_left === 'auto' && $bbox_right === 'auto' && $inner_w !== 'auto') {
					// IF containing element RTL, should set $bbox_right
					$bbox_left = $orig_x0 - $bbox_ml - $cont_x;
					$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
				} elseif ($inner_w === 'auto' && $bbox_right === 'auto' && $bbox_left !== 'auto') {
					// solve for $bbox_right when content_w known - $inner_w=='auto' && $bbox_right=='auto'
				} elseif ($bbox_left === 'auto' && $inner_w !== 'auto' && $bbox_right !== 'auto') {
					$bbox_left = $cont_w - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml - $bbox_right;
				} elseif ($inner_w === 'auto' && $bbox_left !== 'auto' && $bbox_right !== 'auto') {
					$inner_w = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $bbox_pr - $bbox_br - $bbox_ml - $bbox_right;
				} elseif ($bbox_right === 'auto' && $bbox_left !== 'auto' && $inner_w !== 'auto') {
					$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
				}
			}

			// ================================================================
			// ================================================================
			/* -- BACKGROUNDS -- */
			if (isset($pb['BACKGROUND-IMAGE']) && $pb['BACKGROUND-IMAGE']) {
				$ret = $this->SetBackground($pb, $this->blk[1]['inner_width']);
				if ($ret) {
					$this->blk[1]['background-image'] = $ret;
				}
			}
			/* -- END BACKGROUNDS -- */

			$bbox_top_auto = $bbox_top === 'auto';
			$bbox_left_auto = $bbox_left === 'auto';
			$bbox_right_auto = $bbox_right === 'auto';
			$bbox_bottom_auto = $bbox_bottom === 'auto';

			$bbox_top = is_numeric($bbox_top) ? $bbox_top : 0;
			$bbox_left = is_numeric($bbox_left) ? $bbox_left : 0;
			$bbox_right = is_numeric($bbox_right) ? $bbox_right : 0;
			$bbox_bottom = is_numeric($bbox_bottom) ? $bbox_bottom : 0;

			$y = $cont_y + $bbox_top + $bbox_mt + $bbox_bt + $bbox_pt;
			$h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;

			$x = $cont_x + $bbox_left + $bbox_ml + $bbox_bl + $bbox_pl;
			$w = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $bbox_pr - $bbox_br - $bbox_mr - $bbox_right;

			// Set (temporary) values for x y w h to do first paint, if values are auto
			if ($inner_h === 'auto' && $bbox_top_auto) {
				$y = $cont_y + $bbox_mt + $bbox_bt + $bbox_pt;
				$h = $cont_h - ($bbox_bottom + $bbox_mt + $bbox_mb + $bbox_bt + $bbox_bb + $bbox_pt + $bbox_pb);
			} elseif ($inner_h === 'auto' && $bbox_bottom_auto) {
				$y = $cont_y + $bbox_top + $bbox_mt + $bbox_bt + $bbox_pt;
				$h = $cont_h - ($bbox_top + $bbox_mt + $bbox_mb + $bbox_bt + $bbox_bb + $bbox_pt + $bbox_pb);
			}
			if ($inner_w === 'auto' && $bbox_left_auto) {
				$x = $cont_x + $bbox_ml + $bbox_bl + $bbox_pl;
				$w = $cont_w - ($bbox_right + $bbox_ml + $bbox_mr + $bbox_bl + $bbox_br + $bbox_pl + $bbox_pr);
			} elseif ($inner_w === 'auto' && $bbox_right_auto) {
				$x = $cont_x + $bbox_left + $bbox_ml + $bbox_bl + $bbox_pl;
				$w = $cont_w - ($bbox_left + $bbox_ml + $bbox_mr + $bbox_bl + $bbox_br + $bbox_pl + $bbox_pr);
			}

			$bbox_y = $cont_y + $bbox_top + $bbox_mt;
			$bbox_x = $cont_x + $bbox_left + $bbox_ml;

			$saved_block1 = $this->blk[1];

			unset($p);
			unset($pb);

			// ================================================================
			if ($inner_w === 'auto') { // do a first write
				$this->lMargin = $x;
				$this->rMargin = $this->w - $w - $x;

				// SET POSITION & FONT VALUES
				$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
				$this->pageoutput[$this->page] = [];
				$this->x = $x;
				$this->y = $y;
				$this->HTMLheaderPageLinks = [];
				$this->HTMLheaderPageAnnots = [];
				$this->HTMLheaderPageForms = [];
				$this->pageBackgrounds = [];
				$this->maxPosR = 0;
				$this->maxPosL = $this->w; // For RTL
				$this->WriteHTML($html, HTMLParserMode::HTML_HEADER_BUFFER);
				$inner_w = $this->maxPosR - $this->lMargin;
				if ($bbox_right_auto) {
					$bbox_right = $cont_w - $bbox_left - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml;
				} elseif ($bbox_left_auto) {
					$bbox_left = $cont_w - $bbox_ml - $bbox_bl - $bbox_pl - $inner_w - $bbox_pr - $bbox_br - $bbox_ml - $bbox_right;
					$bbox_x = $cont_x + $bbox_left + $bbox_ml;
					$inner_x = $bbox_x + $bbox_bl + $bbox_pl;
					$x = $inner_x;
				}

				$w = $inner_w;
				$bbox_y = $cont_y + $bbox_top + $bbox_mt;
				$bbox_x = $cont_x + $bbox_left + $bbox_ml;
			}

			if ($inner_h === 'auto') { // do a first write

				$this->lMargin = $x;
				$this->rMargin = $this->w - $w - $x;

				// SET POSITION & FONT VALUES
				$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
				$this->pageoutput[$this->page] = [];
				$this->x = $x;
				$this->y = $y;
				$this->HTMLheaderPageLinks = [];
				$this->HTMLheaderPageAnnots = [];
				$this->HTMLheaderPageForms = [];
				$this->pageBackgrounds = [];
				$this->WriteHTML($html, HTMLParserMode::HTML_HEADER_BUFFER);
				$inner_h = $this->y - $y;

				if ($overflow != 'hidden' && $overflow != 'visible') { // constrained
					if (($this->y + $bbox_pb + $bbox_bb) > ($cont_y + $cont_h)) {
						$adj = ($this->y + $bbox_pb + $bbox_bb) - ($cont_y + $cont_h);
						$inner_h -= $adj;
					}
				}
				if ($bbox_bottom_auto && $bbox_top_orig === 'auto') {
					$bbox_bottom = $bbox_top = ($cont_h - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb) / 2;
					if ($overflow != 'hidden' && $overflow != 'visible') { // constrained
						if ($bbox_top < 0) {
							$bbox_top = 0;
							$inner_h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
						}
					}
					$bbox_y = $cont_y + $bbox_top + $bbox_mt;
					$inner_y = $bbox_y + $bbox_bt + $bbox_pt;
					$y = $inner_y;
				} elseif ($bbox_bottom_auto) {
					$bbox_bottom = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb;
				} elseif ($bbox_top_auto) {
					$bbox_top = $cont_h - $bbox_mt - $bbox_bt - $bbox_pt - $inner_h - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
					if ($overflow != 'hidden' && $overflow != 'visible') { // constrained
						if ($bbox_top < 0) {
							$bbox_top = 0;
							$inner_h = $cont_h - $bbox_top - $bbox_mt - $bbox_bt - $bbox_pt - $bbox_pb - $bbox_bb - $bbox_mb - $bbox_bottom;
						}
					}
					$bbox_y = $cont_y + $bbox_top + $bbox_mt;
					$inner_y = $bbox_y + $bbox_bt + $bbox_pt;
					$y = $inner_y;
				}
				$h = $inner_h;
				$bbox_y = $cont_y + $bbox_top + $bbox_mt;
				$bbox_x = $cont_x + $bbox_left + $bbox_ml;
			}

			$inner_w = $w;
			$inner_h = $h;
		}

		$this->lMargin = $x;
		$this->rMargin = $this->w - $w - $x;

		// SET POSITION & FONT VALUES
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->pageoutput[$this->page] = [];

		$this->x = $x;
		$this->y = $y;

		$this->HTMLheaderPageLinks = [];
		$this->HTMLheaderPageAnnots = [];
		$this->HTMLheaderPageForms = [];

		$this->pageBackgrounds = [];

		$this->WriteHTML($html, HTMLParserMode::HTML_HEADER_BUFFER);

		$actual_h = $this->y - $y;
		$use_w = $w;
		$use_h = $h;
		$ratio = $actual_h / $use_w;

		if ($overflow != 'hidden' && $overflow != 'visible') {
			$target = $h / $w;
			if ($target > 0) {
				if (($ratio / $target) > 1) {
					$nl = ceil($actual_h / $this->lineheight);
					$l = $use_w * $nl;
					$est_w = sqrt(($l * $this->lineheight) / $target) * 0.8;
					$use_w += ($est_w - $use_w) - ($w / 100);
				}
				$bpcstart = ($ratio / $target);
				$bpcctr = 1;

				while (($ratio / $target) > 1) {
					// @log 'Auto-sizing fixed-position block $bpcctr++

					$this->x = $x;
					$this->y = $y;

					if (($ratio / $target) > 1.5 || ($ratio / $target) < 0.6) {
						$use_w += ($w / $this->incrementFPR1);
					} elseif (($ratio / $target) > 1.2 || ($ratio / $target) < 0.85) {
						$use_w += ($w / $this->incrementFPR2);
					} elseif (($ratio / $target) > 1.1 || ($ratio / $target) < 0.91) {
						$use_w += ($w / $this->incrementFPR3);
					} else {
						$use_w += ($w / $this->incrementFPR4);
					}

					$use_h = $use_w * $target;
					$this->rMargin = $this->w - $use_w - $x;
					$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
					$this->HTMLheaderPageLinks = [];
					$this->HTMLheaderPageAnnots = [];
					$this->HTMLheaderPageForms = [];
					$this->pageBackgrounds = [];
					$this->WriteHTML($html, HTMLParserMode::HTML_HEADER_BUFFER);
					$actual_h = $this->y - $y;
					$ratio = $actual_h / $use_w;
				}
			}
		}

		$shrink_f = $w / $use_w;

		// ================================================================

		$this->pages[$this->page] .= '___BEFORE_BORDERS___';
		$block_s = $this->PrintPageBackgrounds(); // Save to print later inside clipping path
		$this->pageBackgrounds = [];

		// ================================================================

		if ($rotate == 90 || $rotate == -90) { // mPDF 6
			$prerotw = $bbox_bl + $bbox_pl + $inner_w + $bbox_pr + $bbox_br;
			$preroth = $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb;
			$rot_start = " q\n";
			if ($rotate == 90) {
				if ($rot_rpos !== false) {
					$adjw = $prerotw;
				} // width before rotation
				else {
					$adjw = $preroth;
				} // height before rotation
				if ($rot_bpos !== false) {
					$adjh = -$prerotw + $preroth;
				} else {
					$adjh = 0;
				}
			} else {
				if ($rot_rpos !== false) {
					$adjw = $prerotw - $preroth;
				} else {
					$adjw = 0;
				}
				if ($rot_bpos !== false) {
					$adjh = $preroth;
				} // height before rotation
				else {
					$adjh = $prerotw;
				} // width before rotation
			}
			$rot_start .= $this->transformTranslate($adjw, $adjh, true) . "\n";
			$rot_start .= $this->transformRotate($rotate, $bbox_x, $bbox_y, true) . "\n";
			$rot_end = " Q\n";
		} elseif ($rotate == 180) { // mPDF 6
			$rot_start = " q\n";
			$rot_start .= $this->transformTranslate($bbox_bl + $bbox_pl + $inner_w + $bbox_pr + $bbox_br, $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb, true) . "\n";
			$rot_start .= $this->transformRotate(180, $bbox_x, $bbox_y, true) . "\n";
			$rot_end = " Q\n";
		} else {
			$rot_start = '';
			$rot_end = '';
		}

		// ================================================================
		if (!empty($bounding)) {
			// WHEN HEIGHT // BOTTOM EDGE IS KNOWN and $this->y is set to the bottom
			// Re-instate saved $this->blk[1]
			$this->blk[1] = $saved_block1;

			// These are only needed when painting border/background
			$this->blk[1]['width'] = $bbox_w = $cont_w - $bbox_left - $bbox_ml - $bbox_mr - $bbox_right;
			$this->blk[1]['x0'] = $bbox_x;
			$this->blk[1]['y0'] = $bbox_y;
			$this->blk[1]['startpage'] = $this->page;
			$this->blk[1]['y1'] = $bbox_y + $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb;
			$this->writer->write($rot_start);
			$this->PaintDivBB('', 0, 1); // Prints borders and sets backgrounds in $this->pageBackgrounds
			$this->writer->write($rot_end);
		}

		$s = $this->PrintPageBackgrounds();
		$s = $rot_start . $s . $rot_end;
		$this->pages[$this->page] = preg_replace('/___BEFORE_BORDERS___/', "\n" . $s . "\n", $this->pages[$this->page]);
		$this->pageBackgrounds = [];

		$this->writer->write($rot_start);

		// Clipping Output
		if ($overflow == 'hidden') {
			// Bounding rectangle to clip
			$clip_y1 = $this->y;
			if (!empty($bounding) && ($this->y + $bbox_pb + $bbox_bb) > ($bbox_y + $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb )) {
				$clip_y1 = ($bbox_y + $bbox_bt + $bbox_pt + $inner_h + $bbox_pb + $bbox_bb ) - ($bbox_pb + $bbox_bb);
			}
			// $op = 'W* n';	// Clipping
			$op = 'W n'; // Clipping alternative mode
			$this->writer->write("q");
			$ch = $clip_y1 - $y;
			$this->writer->write(sprintf('%.3F %.3F %.3F %.3F re %s', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $w * Mpdf::SCALE, -$ch * Mpdf::SCALE, $op));
			if (!empty($block_s)) {
				$tmp = "q\n" . sprintf('%.3F %.3F %.3F %.3F re %s', $x * Mpdf::SCALE, ($this->h - $y) * Mpdf::SCALE, $w * Mpdf::SCALE, -$ch * Mpdf::SCALE, $op);
				$tmp .= "\n" . $block_s . "\nQ";
				$block_s = $tmp;
			}
		}


		if (!empty($block_s)) {
			if ($shrink_f != 1) { // i.e. autofit has resized the box
				$tmp = "q\n" . $this->transformScale(($shrink_f * 100), ($shrink_f * 100), $x, $y, true);
				$tmp .= "\n" . $block_s . "\nQ";
				$block_s = $tmp;
			}
			$this->writer->write($block_s);
		}



		if ($shrink_f != 1) { // i.e. autofit has resized the box
			$this->StartTransform();
			$this->transformScale(($shrink_f * 100), ($shrink_f * 100), $x, $y);
		}

		$this->writer->write($this->headerbuffer);

		if ($shrink_f != 1) { // i.e. autofit has resized the box
			$this->StopTransform();
		}

		if ($overflow == 'hidden') {
			// End clipping
			$this->writer->write("Q");
		}

		$this->writer->write($rot_end);


		// Page Links
		foreach ($this->HTMLheaderPageLinks as $lk) {
			if ($rotate) {
				$tmp = $lk[2]; // Switch h - w
				$lk[2] = $lk[3];
				$lk[3] = $tmp;

				$lx1 = (($lk[0] / Mpdf::SCALE));
				$ly1 = (($this->h - ($lk[1] / Mpdf::SCALE)));
				if ($rotate == 90) {
					$adjx = -($lx1 - $bbox_x) + ($preroth - ($ly1 - $bbox_y));
					$adjy = -($ly1 - $bbox_y) + ($lx1 - $bbox_x);
					$lk[2] = -$lk[2];
				} elseif ($rotate == -90) {
					$adjx = -($lx1 - $bbox_x) + ($ly1 - $bbox_y);
					$adjy = -($ly1 - $bbox_y) - ($lx1 - $bbox_x) + $prerotw;
					$lk[3] = -$lk[3];
				}
				if ($rot_rpos !== false) {
					$adjx += $prerotw - $preroth;
				}
				if ($rot_bpos !== false) {
					$adjy += $preroth - $prerotw;
				}
				$lx1 += $adjx;
				$ly1 += $adjy;

				$lk[0] = $lx1 * Mpdf::SCALE;
				$lk[1] = ($this->h - $ly1) * Mpdf::SCALE;
			}
			if ($shrink_f != 1) {  // i.e. autofit has resized the box
				$lx1 = (($lk[0] / Mpdf::SCALE) - $x);
				$lx2 = $x + ($lx1 * $shrink_f);
				$lk[0] = $lx2 * Mpdf::SCALE;
				$ly1 = (($this->h - ($lk[1] / Mpdf::SCALE)) - $y);
				$ly2 = $y + ($ly1 * $shrink_f);
				$lk[1] = ($this->h - $ly2) * Mpdf::SCALE;
				$lk[2] *= $shrink_f; // width
				$lk[3] *= $shrink_f; // height
			}
			$this->PageLinks[$this->page][] = $lk;
		}

		foreach ($this->HTMLheaderPageForms as $n => $f) {
			if ($shrink_f != 1) {  // i.e. autofit has resized the box
				$f['x'] = $x + (($f['x'] - $x) * $shrink_f);
				$f['y'] = $y + (($f['y'] - $y) * $shrink_f);
				$f['w'] *= $shrink_f;
				$f['h'] *= $shrink_f;
				$f['style']['fontsize'] *= $shrink_f;
			}
			$this->form->forms[$f['n']] = $f;
		}
		// Page Annotations
		foreach ($this->HTMLheaderPageAnnots as $lk) {
			if ($rotate) {
				if ($rotate == 90) {
					$adjx = -($lk['x'] - $bbox_x) + ($preroth - ($lk['y'] - $bbox_y));
					$adjy = -($lk['y'] - $bbox_y) + ($lk['x'] - $bbox_x);
				} elseif ($rotate == -90) {
					$adjx = -($lk['x'] - $bbox_x) + ($lk['y'] - $bbox_y);
					$adjy = -($lk['y'] - $bbox_y) - ($lk['x'] - $bbox_x) + $prerotw;
				}
				if ($rot_rpos !== false) {
					$adjx += $prerotw - $preroth;
				}
				if ($rot_bpos !== false) {
					$adjy += $preroth - $prerotw;
				}
				$lk['x'] += $adjx;
				$lk['y'] += $adjy;
			}
			if ($shrink_f != 1) {  // i.e. autofit has resized the box
				$lk['x'] = $x + (($lk['x'] - $x) * $shrink_f);
				$lk['y'] = $y + (($lk['y'] - $y) * $shrink_f);
			}
			$this->PageAnnots[$this->page][] = $lk;
		}

		// Restore
		$this->headerbuffer = '';
		$this->HTMLheaderPageLinks = [];
		$this->HTMLheaderPageAnnots = [];
		$this->HTMLheaderPageForms = [];
		$this->pageBackgrounds = $save_bgs;
		$this->writingHTMLheader = false;

		$this->writingHTMLfooter = false;
		$this->fullImageHeight = false;
		$this->ResetMargins();
		$this->pgwidth = $this->w - $this->lMargin - $this->rMargin;
		$this->SetXY($save_x, $save_y);
		$this->title2annots = $save_annots; // *ANNOTATIONS*
		$this->InFooter = false; // turns back on autopagebreaks
		$this->pageoutput[$this->page] = [];
		$this->pageoutput[$this->page]['Font'] = '';
		/* -- COLUMNS -- */
		if ($save_cols) {
			$this->SetColumns($save_nbcol, $this->colvAlign, $this->ColGap);
		}
		/* -- END COLUMNS -- */
	}

	/* -- END CSS-POSITION -- */

	function initialiseBlock(&$blk)
	{
		$blk['margin_top'] = 0;
		$blk['margin_left'] = 0;
		$blk['margin_bottom'] = 0;
		$blk['margin_right'] = 0;
		$blk['padding_top'] = 0;
		$blk['padding_left'] = 0;
		$blk['padding_bottom'] = 0;
		$blk['padding_right'] = 0;
		$blk['border_top']['w'] = 0;
		$blk['border_left']['w'] = 0;
		$blk['border_bottom']['w'] = 0;
		$blk['border_right']['w'] = 0;
		$blk['direction'] = 'ltr';
		$blk['hide'] = false;
		$blk['outer_left_margin'] = 0;
		$blk['outer_right_margin'] = 0;
		$blk['cascadeCSS'] = [];
		$blk['block-align'] = false;
		$blk['bgcolor'] = false;
		$blk['page_break_after_avoid'] = false;
		$blk['keep_block_together'] = false;
		$blk['float'] = false;
		$blk['line_height'] = '';
		$blk['margin_collapse'] = false;
	}

	function border_details($bd)
	{
		$prop = preg_split('/\s+/', trim($bd));

		if (isset($this->blk[$this->blklvl]['inner_width'])) {
			$refw = $this->blk[$this->blklvl]['inner_width'];
		} elseif (isset($this->blk[$this->blklvl - 1]['inner_width'])) {
			$refw = $this->blk[$this->blklvl - 1]['inner_width'];
		} else {
			$refw = $this->w;
		}
		if (count($prop) == 1) {
			$bsize = $this->sizeConverter->convert($prop[0], $refw, $this->FontSize, false);
			if ($bsize > 0) {
				return ['s' => 1, 'w' => $bsize, 'c' => $this->colorConverter->convert(0, $this->PDFAXwarnings), 'style' => 'solid'];
			} else {
				return ['w' => 0, 's' => 0];
			}
		} elseif (count($prop) == 2) {
			// 1px solid
			if (in_array($prop[1], $this->borderstyles) || $prop[1] == 'none' || $prop[1] == 'hidden') {
				$prop[2] = '';
			} // solid #000000
			elseif (in_array($prop[0], $this->borderstyles) || $prop[0] == 'none' || $prop[0] == 'hidden') {
				$prop[0] = '';
				$prop[1] = $prop[0];
				$prop[2] = $prop[1];
			} // 1px #000000
			else {
				$prop[1] = '';
				$prop[2] = $prop[1];
			}
		} elseif (count($prop) == 3) {
			// Change #000000 1px solid to 1px solid #000000 (proper)
			if (substr($prop[0], 0, 1) == '#') {
				$tmp = $prop[0];
				$prop[0] = $prop[1];
				$prop[1] = $prop[2];
				$prop[2] = $tmp;
			} // Change solid #000000 1px to 1px solid #000000 (proper)
			elseif (substr($prop[0], 1, 1) == '#') {
				$tmp = $prop[1];
				$prop[0] = $prop[2];
				$prop[1] = $prop[0];
				$prop[2] = $tmp;
			} // Change solid 1px #000000 to 1px solid #000000 (proper)
			elseif (in_array($prop[0], $this->borderstyles) || $prop[0] == 'none' || $prop[0] == 'hidden') {
				$tmp = $prop[0];
				$prop[0] = $prop[1];
				$prop[1] = $tmp;
			}
		} else {
			return ['w' => 0, 's' => 0];
		}
		// Size
		$bsize = $this->sizeConverter->convert($prop[0], $refw, $this->FontSize, false);
		// color
		$coul = $this->colorConverter->convert($prop[2], $this->PDFAXwarnings); // returns array
		// Style
		$prop[1] = strtolower($prop[1]);
		if (in_array($prop[1], $this->borderstyles) && $bsize > 0) {
			$on = 1;
		} elseif ($prop[1] == 'hidden') {
			$on = 1;
			$bsize = 0;
			$coul = '';
		} elseif ($prop[1] == 'none') {
			$on = 0;
			$bsize = 0;
			$coul = '';
		} else {
			$on = 0;
			$bsize = 0;
			$coul = '';
			$prop[1] = '';
		}
		return ['s' => $on, 'w' => $bsize, 'c' => $coul, 'style' => $prop[1], 'dom' => 0];
	}

	/* -- END HTML-CSS -- */


	/* -- BORDER-RADIUS -- */

	function _borderPadding($a, $b, &$px, &$py)
	{
		// $px and py are padding long axis (x) and short axis (y)
		$added = 0; // extra padding

		$x = $a - $px;
		$y = $b - $py;
		// Check if Falls within ellipse of border radius
		if (( (($x + $added) * ($x + $added)) / ($a * $a) + (($y + $added) * ($y + $added)) / ($b * $b) ) <= 1) {
			return false;
		}

		$t = atan2($y, $x);

		$newx = $b / sqrt((($b * $b) / ($a * $a)) + ( tan($t) * tan($t) ));
		$newy = $a / sqrt((($a * $a) / ($b * $b)) + ( (1 / tan($t)) * (1 / tan($t)) ));
		$px = max($px, $a - $newx + $added);
		$py = max($py, $b - $newy + $added);
	}

	/* -- END BORDER-RADIUS -- */
	/* -- HTML-CSS -- */
	/* -- CSS-PAGE -- */

	function SetPagedMediaCSS($name, $first, $oddEven)
	{
		if ($oddEven == 'E') {
			if ($this->directionality == 'rtl') {
				$side = 'R';
			} else {
				$side = 'L';
			}
		} else {
			if ($this->directionality == 'rtl') {
				$side = 'L';
			} else {
				$side = 'R';
			}
		}
		$name = strtoupper($name);
		$p = [];
		$p['SIZE'] = 'AUTO';

		// Uses mPDF original margins as default
		$p['MARGIN-RIGHT'] = strval($this->orig_rMargin) . 'mm';
		$p['MARGIN-LEFT'] = strval($this->orig_lMargin) . 'mm';
		$p['MARGIN-TOP'] = strval($this->orig_tMargin) . 'mm';
		$p['MARGIN-BOTTOM'] = strval($this->orig_bMargin) . 'mm';
		$p['MARGIN-HEADER'] = strval($this->orig_hMargin) . 'mm';
		$p['MARGIN-FOOTER'] = strval($this->orig_fMargin) . 'mm';

		// Basic page + selector
		if (isset($this->cssManager->CSS['@PAGE'])) {
			$zp = $this->cssManager->CSS['@PAGE'];
		} else {
			$zp = [];
		}
		if (is_array($zp) && !empty($zp)) {
			$p = array_merge($p, $zp);
		}

		if (isset($p['EVEN-HEADER-NAME']) && $oddEven == 'E') {
			$p['HEADER'] = $p['EVEN-HEADER-NAME'];
			unset($p['EVEN-HEADER-NAME']);
		}
		if (isset($p['ODD-HEADER-NAME']) && $oddEven != 'E') {
			$p['HEADER'] = $p['ODD-HEADER-NAME'];
			unset($p['ODD-HEADER-NAME']);
		}
		if (isset($p['EVEN-FOOTER-NAME']) && $oddEven == 'E') {
			$p['FOOTER'] = $p['EVEN-FOOTER-NAME'];
			unset($p['EVEN-FOOTER-NAME']);
		}
		if (isset($p['ODD-FOOTER-NAME']) && $oddEven != 'E') {
			$p['FOOTER'] = $p['ODD-FOOTER-NAME'];
			unset($p['ODD-FOOTER-NAME']);
		}

		// If right/Odd page
		if (isset($this->cssManager->CSS['@PAGE>>PSEUDO>>RIGHT']) && $side == 'R') {
			$zp = $this->cssManager->CSS['@PAGE>>PSEUDO>>RIGHT'];
		} else {
			$zp = [];
		}
		if (isset($zp['SIZE'])) {
			unset($zp['SIZE']);
		}
		if (isset($zp['SHEET-SIZE'])) {
			unset($zp['SHEET-SIZE']);
		}
		// Disallow margin-left or -right on :LEFT or :RIGHT
		if (isset($zp['MARGIN-LEFT'])) {
			unset($zp['MARGIN-LEFT']);
		}
		if (isset($zp['MARGIN-RIGHT'])) {
			unset($zp['MARGIN-RIGHT']);
		}
		if (is_array($zp) && !empty($zp)) {
			$p = array_merge($p, $zp);
		}

		// If left/Even page
		if (isset($this->cssManager->CSS['@PAGE>>PSEUDO>>LEFT']) && $side == 'L') {
			$zp = $this->cssManager->CSS['@PAGE>>PSEUDO>>LEFT'];
		} else {
			$zp = [];
		}
		if (isset($zp['SIZE'])) {
			unset($zp['SIZE']);
		}
		if (isset($zp['SHEET-SIZE'])) {
			unset($zp['SHEET-SIZE']);
		}
		// Disallow margin-left or -right on :LEFT or :RIGHT
		if (isset($zp['MARGIN-LEFT'])) {
			unset($zp['MARGIN-LEFT']);
		}
		if (isset($zp['MARGIN-RIGHT'])) {
			unset($zp['MARGIN-RIGHT']);
		}
		if (is_array($zp) && !empty($zp)) {
			$p = array_merge($p, $zp);
		}

		// If first page
		if (isset($this->cssManager->CSS['@PAGE>>PSEUDO>>FIRST']) && $first) {
			$zp = $this->cssManager->CSS['@PAGE>>PSEUDO>>FIRST'];
		} else {
			$zp = [];
		}
		if (isset($zp['SIZE'])) {
			unset($zp['SIZE']);
		}
		if (isset($zp['SHEET-SIZE'])) {
			unset($zp['SHEET-SIZE']);
		}
		// Disallow margin-left or -right on :FIRST	// mPDF 5.7.3
		if (isset($zp['MARGIN-LEFT'])) {
			unset($zp['MARGIN-LEFT']);
		}
		if (isset($zp['MARGIN-RIGHT'])) {
			unset($zp['MARGIN-RIGHT']);
		}
		if (is_array($zp) && !empty($zp)) {
			$p = array_merge($p, $zp);
		}

		// If named page
		if ($name) {
			if (isset($this->cssManager->CSS['@PAGE>>NAMED>>' . $name])) {
				$zp = $this->cssManager->CSS['@PAGE>>NAMED>>' . $name];
			} else {
				$zp = [];
			}
			if (is_array($zp) && !empty($zp)) {
				$p = array_merge($p, $zp);
			}

			if (isset($p['EVEN-HEADER-NAME']) && $oddEven == 'E') {
				$p['HEADER'] = $p['EVEN-HEADER-NAME'];
				unset($p['EVEN-HEADER-NAME']);
			}
			if (isset($p['ODD-HEADER-NAME']) && $oddEven != 'E') {
				$p['HEADER'] = $p['ODD-HEADER-NAME'];
				unset($p['ODD-HEADER-NAME']);
			}
			if (isset($p['EVEN-FOOTER-NAME']) && $oddEven == 'E') {
				$p['FOOTER'] = $p['EVEN-FOOTER-NAME'];
				unset($p['EVEN-FOOTER-NAME']);
			}
			if (isset($p['ODD-FOOTER-NAME']) && $oddEven != 'E') {
				$p['FOOTER'] = $p['ODD-FOOTER-NAME'];
				unset($p['ODD-FOOTER-NAME']);
			}

			// If named right/Odd page
			if (isset($this->cssManager->CSS['@PAGE>>NAMED>>' . $name . '>>PSEUDO>>RIGHT']) && $side == 'R') {
				$zp = $this->cssManager->CSS['@PAGE>>NAMED>>' . $name . '>>PSEUDO>>RIGHT'];
			} else {
				$zp = [];
			}
			if (isset($zp['SIZE'])) {
				unset($zp['SIZE']);
			}
			if (isset($zp['SHEET-SIZE'])) {
				unset($zp['SHEET-SIZE']);
			}
			// Disallow margin-left or -right on :LEFT or :RIGHT
			if (isset($zp['MARGIN-LEFT'])) {
				unset($zp['MARGIN-LEFT']);
			}
			if (isset($zp['MARGIN-RIGHT'])) {
				unset($zp['MARGIN-RIGHT']);
			}
			if (is_array($zp) && !empty($zp)) {
				$p = array_merge($p, $zp);
			}

			// If named left/Even page
			if (isset($this->cssManager->CSS['@PAGE>>NAMED>>' . $name . '>>PSEUDO>>LEFT']) && $side == 'L') {
				$zp = $this->cssManager->CSS['@PAGE>>NAMED>>' . $name . '>>PSEUDO>>LEFT'];
			} else {
				$zp = [];
			}
			if (isset($zp['SIZE'])) {
				unset($zp['SIZE']);
			}
			if (isset($zp['SHEET-SIZE'])) {
				unset($zp['SHEET-SIZE']);
			}
			// Disallow margin-left or -right on :LEFT or :RIGHT
			if (isset($zp['MARGIN-LEFT'])) {
				unset($zp['MARGIN-LEFT']);
			}
			if (isset($zp['MARGIN-RIGHT'])) {
				unset($zp['MARGIN-RIGHT']);
			}
			if (is_array($zp) && !empty($zp)) {
				$p = array_merge($p, $zp);
			}

			// If named first page
			if (isset($this->cssManager->CSS['@PAGE>>NAMED>>' . $name . '>>PSEUDO>>FIRST']) && $first) {
				$zp = $this->cssManager->CSS['@PAGE>>NAMED>>' . $name . '>>PSEUDO>>FIRST'];
			} else {
				$zp = [];
			}
			if (isset($zp['SIZE'])) {
				unset($zp['SIZE']);
			}
			if (isset($zp['SHEET-SIZE'])) {
				unset($zp['SHEET-SIZE']);
			}
			// Disallow margin-left or -right on :FIRST	// mPDF 5.7.3
			if (isset($zp['MARGIN-LEFT'])) {
				unset($zp['MARGIN-LEFT']);
			}
			if (isset($zp['MARGIN-RIGHT'])) {
				unset($zp['MARGIN-RIGHT']);
			}
			if (is_array($zp) && !empty($zp)) {
				$p = array_merge($p, $zp);
			}
		}

		$orientation = $mgl = $mgr = $mgt = $mgb = $mgh = $mgf = '';
		$header = $footer = '';
		$resetpagenum = $pagenumstyle = $suppress = '';
		$marks = '';
		$bg = [];

		$newformat = '';


		if (isset($p['SHEET-SIZE']) && is_array($p['SHEET-SIZE'])) {
			$newformat = $p['SHEET-SIZE'];
			if ($newformat[0] > $newformat[1]) { // landscape
				$newformat = array_reverse($newformat);
				$p['ORIENTATION'] = 'L';
			} else {
				$p['ORIENTATION'] = 'P';
			}
			$this->_setPageSize($newformat, $p['ORIENTATION']);
		}

		if (isset($p['SIZE']) && is_array($p['SIZE']) && !$newformat) {
			if ($p['SIZE']['W'] > $p['SIZE']['H']) {
				$p['ORIENTATION'] = 'L';
			} else {
				$p['ORIENTATION'] = 'P';
			}
		}
		if (is_array($p['SIZE'])) {
			if ($p['SIZE']['W'] > $this->fw) {
				$p['SIZE']['W'] = $this->fw;
			} // mPD 4.2 use fw not fPt
			if ($p['SIZE']['H'] > $this->fh) {
				$p['SIZE']['H'] = $this->fh;
			}
			if (($p['ORIENTATION'] == $this->DefOrientation && !$newformat) || ($newformat && $p['ORIENTATION'] == 'P')) {
				$outer_width_LR = ($this->fw - $p['SIZE']['W']) / 2;
				$outer_width_TB = ($this->fh - $p['SIZE']['H']) / 2;
			} else {
				$outer_width_LR = ($this->fh - $p['SIZE']['W']) / 2;
				$outer_width_TB = ($this->fw - $p['SIZE']['H']) / 2;
			}
			$pgw = $p['SIZE']['W'];
			$pgh = $p['SIZE']['H'];
		} else { // AUTO LANDSCAPE PORTRAIT
			$outer_width_LR = 0;
			$outer_width_TB = 0;
			if (!$newformat) {
				if (strtoupper($p['SIZE']) == 'AUTO') {
					$p['ORIENTATION'] = $this->DefOrientation;
				} elseif (strtoupper($p['SIZE']) == 'LANDSCAPE') {
					$p['ORIENTATION'] = 'L';
				} else {
					$p['ORIENTATION'] = 'P';
				}
			}
			if (($p['ORIENTATION'] == $this->DefOrientation && !$newformat) || ($newformat && $p['ORIENTATION'] == 'P')) {
				$pgw = $this->fw;
				$pgh = $this->fh;
			} else {
				$pgw = $this->fh;
				$pgh = $this->fw;
			}
		}

		if (isset($p['HEADER']) && $p['HEADER']) {
			$header = $p['HEADER'];
		}
		if (isset($p['FOOTER']) && $p['FOOTER']) {
			$footer = $p['FOOTER'];
		}
		if (isset($p['RESETPAGENUM']) && $p['RESETPAGENUM']) {
			$resetpagenum = $p['RESETPAGENUM'];
		}
		if (isset($p['PAGENUMSTYLE']) && $p['PAGENUMSTYLE']) {
			$pagenumstyle = $p['PAGENUMSTYLE'];
		}
		if (isset($p['SUPPRESS']) && $p['SUPPRESS']) {
			$suppress = $p['SUPPRESS'];
		}

		if (isset($p['MARKS'])) {
			if (preg_match('/cross/i', $p['MARKS']) && preg_match('/crop/i', $p['MARKS'])) {
				$marks = 'CROPCROSS';
			} elseif (strtoupper($p['MARKS']) == 'CROP') {
				$marks = 'CROP';
			} elseif (strtoupper($p['MARKS']) == 'CROSS') {
				$marks = 'CROSS';
			}
		}

		if (isset($p['BACKGROUND-COLOR']) && $p['BACKGROUND-COLOR']) {
			$bg['BACKGROUND-COLOR'] = $p['BACKGROUND-COLOR'];
		}
		/* -- BACKGROUNDS -- */
		if (isset($p['BACKGROUND-GRADIENT']) && $p['BACKGROUND-GRADIENT']) {
			$bg['BACKGROUND-GRADIENT'] = $p['BACKGROUND-GRADIENT'];
		}
		if (isset($p['BACKGROUND-IMAGE']) && $p['BACKGROUND-IMAGE']) {
			$bg['BACKGROUND-IMAGE'] = $p['BACKGROUND-IMAGE'];
		}
		if (isset($p['BACKGROUND-REPEAT']) && $p['BACKGROUND-REPEAT']) {
			$bg['BACKGROUND-REPEAT'] = $p['BACKGROUND-REPEAT'];
		}
		if (isset($p['BACKGROUND-POSITION']) && $p['BACKGROUND-POSITION']) {
			$bg['BACKGROUND-POSITION'] = $p['BACKGROUND-POSITION'];
		}
		if (isset($p['BACKGROUND-IMAGE-RESIZE']) && $p['BACKGROUND-IMAGE-RESIZE']) {
			$bg['BACKGROUND-IMAGE-RESIZE'] = $p['BACKGROUND-IMAGE-RESIZE'];
		}
		if (isset($p['BACKGROUND-IMAGE-OPACITY'])) {
			$bg['BACKGROUND-IMAGE-OPACITY'] = $p['BACKGROUND-IMAGE-OPACITY'];
		}
		/* -- END BACKGROUNDS -- */

		if (isset($p['MARGIN-LEFT'])) {
			$mgl = $this->sizeConverter->convert($p['MARGIN-LEFT'], $pgw) + $outer_width_LR;
		}
		if (isset($p['MARGIN-RIGHT'])) {
			$mgr = $this->sizeConverter->convert($p['MARGIN-RIGHT'], $pgw) + $outer_width_LR;
		}
		if (isset($p['MARGIN-BOTTOM'])) {
			$mgb = $this->sizeConverter->convert($p['MARGIN-BOTTOM'], $pgh) + $outer_width_TB;
		}
		if (isset($p['MARGIN-TOP'])) {
			$mgt = $this->sizeConverter->convert($p['MARGIN-TOP'], $pgh) + $outer_width_TB;
		}
		if (isset($p['MARGIN-HEADER'])) {
			$mgh = $this->sizeConverter->convert($p['MARGIN-HEADER'], $pgh) + $outer_width_TB;
		}
		if (isset($p['MARGIN-FOOTER'])) {
			$mgf = $this->sizeConverter->convert($p['MARGIN-FOOTER'], $pgh) + $outer_width_TB;
		}

		if (isset($p['ORIENTATION']) && $p['ORIENTATION']) {
			$orientation = $p['ORIENTATION'];
		}
		$this->page_box['outer_width_LR'] = $outer_width_LR; // Used in MARKS:crop etc.
		$this->page_box['outer_width_TB'] = $outer_width_TB;

		return [$orientation, $mgl, $mgr, $mgt, $mgb, $mgh, $mgf, $header, $footer, $bg, $resetpagenum, $pagenumstyle, $suppress, $marks, $newformat];
	}

	/* -- END CSS-PAGE -- */



	/* -- CSS-FLOAT -- */

	// Added mPDF 3.0 Float DIV - CLEAR
	function ClearFloats($clear, $blklvl = 0)
	{
		list($l_exists, $r_exists, $l_max, $r_max, $l_width, $r_width) = $this->GetFloatDivInfo($blklvl, true);
		$end = $currpos = ($this->page * 1000 + $this->y);
		if ($clear == 'BOTH' && ($l_exists || $r_exists)) {
			$this->pageoutput[$this->page] = [];
			$end = max($l_max, $r_max, $currpos);
		} elseif ($clear == 'RIGHT' && $r_exists) {
			$this->pageoutput[$this->page] = [];
			$end = max($r_max, $currpos);
		} elseif ($clear == 'LEFT' && $l_exists) {
			$this->pageoutput[$this->page] = [];
			$end = max($l_max, $currpos);
		} else {
			return;
		}
		$old_page = $this->page;
		$new_page = intval($end / 1000);
		if ($old_page != $new_page) {
			$s = $this->PrintPageBackgrounds();
			// Writes after the marker so not overwritten later by page background etc.
			$this->pages[$this->page] = preg_replace('/(___BACKGROUND___PATTERNS' . $this->uniqstr . ')/', '\\1' . "\n" . $s . "\n", $this->pages[$this->page]);
			$this->pageBackgrounds = [];
			$this->page = $new_page;
		}
		$this->ResetMargins();
		$this->pageoutput[$this->page] = [];

		$this->y = (round($end * 1000) % 1000000) / 1000; // mod changes operands to integers before processing
	}

	// Added mPDF 3.0 Float DIV
	function GetFloatDivInfo($blklvl = 0, $clear = false)
	{
		// If blklvl specified, only returns floats at that level - for ClearFloats
		$l_exists = false;
		$r_exists = false;
		$l_max = 0;
		$r_max = 0;
		$l_width = 0;
		$r_width = 0;
		if (count($this->floatDivs)) {
			$currpos = ($this->page * 1000 + $this->y);
			foreach ($this->floatDi