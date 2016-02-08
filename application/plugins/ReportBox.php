<?php

/**
 * PHPReport
 * Library for generating reports from PHP
 * Copyright (c) 2012 PHPReport
 * 
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 *
 * @package PHPReport
 * @author Vernes Šiljegović
 * @copyright  Copyright (c) 2012 PHPReport
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt	LGPL
 * @version 1.0, 2012-03-04
 */
/**
 * PHPExcel
 *
 * @copyright  Copyright (c) 2006 - 2011 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
require_once 'PHPExcel.php';

class Default_Plugin_ReportBox {

    //report template
    private $_templateDir;
    private $_template;
    private $_usingTemplate;
    //internal collections of data
    private $_data = array();
    private $_search = array();
    private $_replace = array();
    private $_group = array();
    //parameters
    private $_renderHeading = false;
    private $_renderFooting = false;
    private $_useStripRows = false;
    private $_headingText;
    private $_footingText;
    private $_noResultText;
    private $_isFooter = false;
    //parameters SetPage
    private $_paperSize = 9; // Paper size: "PAPERSIZE_A4 = 9"; "PAPERSIZE_A3 = 8"
    private $_orientation = 'default'; // Orientation: "default"; "landscape"; "portrait"
    //styling
    private $_cellStyleArray = array(
        'font' => array(
            'size' => '9',
            'font-family' => 'Arial, Helvetica, sans-serif'
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'F2F2F2' // 4E5A7A FFEBA5 F2F2F2  #E4E8F3
            )
        ),
        'borders' => array(
            'top' => array(
                'style' => PHPExcel_Style_Border::BORDER_DOTTED, //PHPExcel_Style_Border::BORDER_THIN,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_DOTTED,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'left' => array(
                'style' => PHPExcel_Style_Border::BORDER_DOTTED,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'right' => array(
                'style' => PHPExcel_Style_Border::BORDER_DOTTED,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
        ),
    );
    private $_headerStyleArray = array(
        'font' => array(
            'bold' => true,
            'size' => '10',
            'font-family' => 'Arial, Helvetica, sans-serif',
            'color' => array(
                'rgb' => '000000'//E4E8F3 FFFFFF
            )
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'E4E8F3' // 4E5A7A FFEBA5 F2F2F2  #E4E8F3
            )
        ),
        'borders' => array(
            'top' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'left' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'right' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
        ),
    );
    private $_footerStyleArray = array(
        'font' => array(
            'bold' => true,
            'size' => '10',
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'E4E8F3',
            )
        ),
        'borders' => array(
            'top' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'left' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'right' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
        ),
    );
    private $_headerGroupStyleArray = array(
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
        ),
        'font' => array(
            'bold' => true
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => '8DB4E3'
            )
        ),
        'borders' => array(
            'top' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'bottom' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'left' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
            'right' => array(
                'style' => PHPExcel_Style_Border::BORDER_MEDIUM,
                'color' => array(
                    'rgb' => '808080'
                )
            ),
        ),
    );
    private $_footerGroupStyleArray = array(
        'font' => array(
            'bold' => true
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'C5D9F1'
            )
        )
    );
    private $_noResultStyleArray = array(
        'borders' => array(
            'outline' => array(
                'style' => PHPExcel_Style_Border::BORDER_THIN
            )
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ),
        'font' => array(
            'bold' => true
        ),
        'fill' => array(
            'type' => PHPExcel_Style_Fill::FILL_SOLID,
            'startcolor' => array(
                'rgb' => 'FFEBA5'
            )
        )
    );
    private $_headingStyleArray = array(
        'font' => array(
            'bold' => true,
            'color' => array(
                'rgb' => '4E5A7A'
            ),
            'size' => '12'
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER
        ),
    );
    private $_footingStyleArray = array(
        'font' => array(
            'bold' => false,
            'color' => array(
                'rgb' => '4E5A7A'
            ),
            'size' => '10'
        ),
        'alignment' => array(
            'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_LEFT
        ),
    );
    //PHPExcel objects
    private $objReader;
    private $objPHPExcel;
    private $objWorksheet;
    private $objWriter;

    /**
     * Creates new report with some configuration parameters
     * @param array $config 
     */
    public function __construct($config = array()) {
        $this->setConfig($config);
        $this->init();
    }

    //==== INI REPORT ========//

    /**
     * Uses configuration array to adjust report parameters
     * @param array $config 
     */
    public function setConfig($config) {
        if (!is_array($config))
            throw new Exception('Unable to use non-array configuration');

        foreach ($config as $key => $value) {
            $_key = '_' . $key;
            $this->$_key = $value;
        }
    }

    /**
     * Get config value
     * 
     * @param string $key
     * @return mixed
     */
    public function getConfig($key) {
        $_key = '_' . $key;
        return $this->$_key;
    }

    /**
     * Initializes internal objects 
     */
    private function init() {
        if ($this->_template != '') {
            $this->loadTemplate();
        } elseif ($this->_filename != '') {
            $this->loadFile();
        } else {
            $this->createTemplate();
        }
    }

    /**
     * Load file
     */
    public function loadFile($filename = '') {
        if ($filename != '')
            $this->_filename = $template;

        if (!is_file($this->_filename))
            throw new Exception('Unable to load file: ' . $this->_filename);

        //identify type of template file
        $inputFileType = PHPExcel_IOFactory::identify($this->_filename);
        //TODO: better control of allowed input types
        //load template file into PHPExcel objects
        $this->objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $this->objReader->setReadDataOnly(false);
        $this->objPHPExcel = $this->objReader->load($this->_filename);
        $this->objWorksheet = $this->objPHPExcel->getActiveSheet();

        $this->_usingFile = true;
    }

    /**
     * Loads Excel file as a template for report
     */
    public function loadTemplate($template = '') {
        if ($template != '')
            $this->_template = $template;

        if (!is_file($this->_templateDir . $this->_template))
            throw new Exception('Unable to load template file: ' . $this->_templateDir . $this->_template);

        //identify type of template file
        $inputFileType = PHPExcel_IOFactory::identify($this->_templateDir . $this->_template);
        //TODO: better control of allowed input types
        //load template file into PHPExcel objects
        $this->objReader = PHPExcel_IOFactory::createReader($inputFileType);
        $this->objPHPExcel = $this->objReader->load($this->_templateDir . $this->_template);
        $this->objWorksheet = $this->objPHPExcel->getActiveSheet();

        $this->_usingTemplate = true;
    }

    /**
     * Creates PHPExcel object and template for report
     */
    private function createTemplate() {
        $this->objPHPExcel = new PHPExcel();
        $this->objPHPExcel->setActiveSheetIndex(0);
        $this->objWorksheet = $this->objPHPExcel->getActiveSheet();

        $this->_usingTemplate = false;
    }

    /**
     * Takes an array of all the data for report
     * 
     * @param array $dataCollection Associative array with data for report
     * or an array of such arrays
     * id - unique identifier of data group
     * data - Single array of data
     */
    public function load($dataCollection) {
        if (!is_array($dataCollection))
            throw new Exception("Could not load a non-array data!");

        //clear current data
        $this->clearData();

        //check if it is a single array of data
        if (isset($dataCollection['data'])) {
            $this->addData($dataCollection);
        } else {
            //it's an array of arrays of data, add all
            foreach ($dataCollection as $data)
                $this->addData($data);
        }
    }

    /**
     * Takes an array of all the data for report
     * 
     * @param array $data Associative array with two elements
     * id - unique identifier of data group
     * data - Single array of data
     */
    private function addData($data) {
        if (!is_array($data))
            throw new Exception("Could not load a non-array data!");
        if (!isset($data['id']))
            throw new Exception("Every array of data needs an 'id'!");
        if (!isset($data['data']))
            throw new Exception("Loaded array needs an element 'data'!");

        $this->_data[] = $data;
    }

    /**
     * Clears internal collection of data 
     */
    private function clearData() {
        $this->_data = array();
        $this->_renderHeading = FALSE;
        $this->_renderFooting = FALSE;
    }

    /**
     * Set report properties
     * 
     */
    public function setReportProperties() {
        $repProperties = new PHPExcel_DocumentProperties();
        if ($this->getConfig("title")) {
            $title = $this->getConfig("title");
            $repProperties->setTitle($title);
        }
        if ($this->getConfig("creator")) {
            $creator = $this->getConfig("creator");
            $repProperties->setCreator($creator);
        }
        if ($this->getConfig("company")) {
            $company = $this->getConfig("company");
            $repProperties->setCompany($company);
        }

        $this->objPHPExcel->setProperties($repProperties);
    }

    /**
     * Set report page setup
     * 
     */
    public function setReportPageSetup() {//PHPExcel_Worksheet_PageSetup
        $repPageSetup = new PHPExcel_Worksheet_PageSetup();

        // Orientation: "default"; "landscape"; "portrait"
        if ($this->getConfig("orientation")) {
            $orientation = $this->getConfig("orientation");
            $repPageSetup->setOrientation($orientation);
        }
        // Paper size: "PAPERSIZE_A4 = 9"; "PAPERSIZE_A3 = 8"
        if ($this->getConfig("paperSize")) {
            $paperSize = $this->getConfig("paperSize");
            $repPageSetup->setPaperSize($paperSize);
        }
        // Print scaling. Valid values range from 10 to 400
        // This setting is overridden when fitToWidth and/or fitToHeight are in use
        if ($this->getConfig("scale")) {
            $scale = $this->getConfig("scale");
            $repPageSetup->setScale($scale);
        }

        // Whether scale or fitToWith / fitToHeight applies: BOOLEAN
        if ($this->getConfig("fitToPage")) {
            $fitToPage = $this->getConfig("fitToPage");
            $repPageSetup->setFitToPage($fitToPage);
        }
        // Set Fit To Height: INT (1...)
        if ($this->getConfig("fitToHeight")) {
            $fitToHeight = $this->getConfig("fitToHeight");
            $repPageSetup->setFitToHeight($fitToHeight);
        }
        // Set Fit To Width: INT (1...)
        if ($this->getConfig("fitToWidth")) {
            $fitToWidth = $this->getConfig("fitToWidth");
            $repPageSetup->setScale($fitToWidth);
        }
        // Set Columns to repeat at left: Containing start column and end column, empty array if option unset
        if ($this->getConfig("columnsToRepeatAtLeft")) {
            $columnsToRepeatAtLeft = $this->getConfig("columnsToRepeatAtLeft");
            $repPageSetup->setColumnsToRepeatAtLeft($columnsToRepeatAtLeft);
        }
        // Set Rows to repeat at top: Containing start column and end column, empty array if option unset
        if ($this->getConfig("rowsToRepeatAtTop")) {
            $rowsToRepeatAtTop = $this->getConfig("rowsToRepeatAtTop");
            $repPageSetup->setRowsToRepeatAtTop($rowsToRepeatAtTop);
        }

        // Set center page horizontally: BOOLEAN
        if ($this->getConfig("horizontalCentered")) {
            $horizontalCentered = $this->getConfig("horizontalCentered");
            $repPageSetup->setHorizontalCentered($horizontalCentered);
        }
        // Set center page vertically: BOOLEAN
        if ($this->getConfig("verticalCentered")) {
            $verticalCentered = $this->getConfig("verticalCentered");
            $repPageSetup->setVerticalCentered($verticalCentered);
        }


        $this->objWorksheet->setPageSetup($repPageSetup);
    }

    //==== CREATE REPORT ========//

    /**
     * Creates a new report based on loaded data 
     */
    public function createReport() {
        foreach ($this->_data as $data) {
            //$data must have id and data elements
            //$data may also have config, header, footer, group

            $id = $data['id'];
            $format = isset($data['format']) ? $data['format'] : array();
            $config = isset($data['config']) ? $data['config'] : array();
            $group = isset($data['group']) ? $data['group'] : array();

            $configHeader = isset($config['header']) ? $config['header'] : $config;
            $configData = isset($config['data']) ? $config['data'] : $config;
            $configFooter = isset($config['footer']) ? $config['footer'] : $config;

            $config = array(
                'header' => $configHeader,
                'data' => $configData,
                'footer' => $configFooter
            );

            //set the group
            $this->_group = $group;

            $loadCollection = array();

            $arrMergeCells = array();

            $nextRow = $this->objWorksheet->getHighestRow();
            if ($nextRow > 1)
                $nextRow++;

            $startRow = $nextRow;

            //form the header for data
            if (isset($data['header'])) {
                $headers = $data['header'];
                foreach ($headers as $header) {
                    $headerId = "HEADER{$nextRow}_{$id}";
                    $colIndex = -1;
                    foreach ($header as $k => $v) {
                        $colIndex++;
                        $tag = "{" . $headerId . ":" . $k . "}";
                        $this->objWorksheet->setCellValueByColumnAndRow($colIndex, $nextRow, $tag);
                        if (isset($config['header'][$k]['width']))
                            $this->objWorksheet->getColumnDimensionByColumn($colIndex)->setWidth($this->pixel2unit($config['header'][$k]['width']));
                        if (isset($config['header'][$k]['align'])) {
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getAlignment()->setHorizontal($config['header'][$k]['align']);
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getAlignment()->setVertical($config['header'][$k]['align']);
                        }

                        if (isset($config['header'][$k]['borders'])) {
                            $arrBorders = $config['header'][$k]['borders'];
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getLeft()->applyFromArray($arrBorders);
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getRight()->applyFromArray($arrBorders);
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getTop()->applyFromArray($arrBorders);
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getBottom()->applyFromArray($arrBorders);
                        } else {
                            if ($this->_cellStyleArray['borders']) {
                                $arrBorders = $this->_cellStyleArray['borders'];
                                $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->applyFromArray($arrBorders);
                            }
                        }

                        // Занесем в массив координаты ячеек для обьединения
                        if (isset($config['header'][$k]['merge'])) {
                            $arrMergeCells[$k][] = array($colIndex, $nextRow);
                        }
                    }
                    $nextRow++;
                    //add header row to load collection
                    $loadCollection[] = array('id' => $headerId, 'data' => $header);
                }

                // Сделаем обьединение ячеек
                $this->setMergeCells($arrMergeCells);

                // Установим формат ячеек заголовка
                if ($colIndex > -1) {
                    $this->objWorksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $startRow . ':' . PHPExcel_Cell::stringFromColumnIndex($colIndex) . ($nextRow - 1))->applyFromArray($this->_headerStyleArray);
                }

                //move to next row for data
//                $nextRow++;
            }


            //form the data repeating row
            $dataId = 'DATA_' . $id;
            $colIndex = -1;

            //form the template row
            if (count($data['data']) > 0) {
                //we just need first row of data, to see array keys
                $singleDataRow = $data['data'][0];
                foreach ($singleDataRow as $k => $v) {
                    $colIndex++;
                    $tag = "{" . $dataId . ":" . $k . "}";
                    $this->objWorksheet->setCellValueByColumnAndRow($colIndex, $nextRow, $tag);
                    if (isset($config['data'][$k]['align']))
                        $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getAlignment()->setHorizontal($config['data'][$k]['align']);

                    if (isset($config['data'][$k]['borders'])) {
                        $arrBorders = $config['data'][$k]['borders'];
                        $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getLeft()->applyFromArray($arrBorders);
                        $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getRight()->applyFromArray($arrBorders);
                        $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getTop()->applyFromArray($arrBorders);
                        $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getBottom()->applyFromArray($arrBorders);
                    } else {
                        if ($this->_cellStyleArray['borders']) {
                            $arrBorders = $this->_cellStyleArray['borders'];
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->applyFromArray($arrBorders);

                            // Установим бардюры для обьединенных ячеек
                            $this->setMergeCellsBorders($arrMergeCells);
                        }
                    }
                }
            }

            //add this row to collection for load but with repeating
            $loadCollection[] = array('id' => $dataId, 'data' => $data['data'], 'repeat' => true, 'format' => $format);
            $this->enableStripRows();

            //form the footer row for data if needed
            $arrMergeCells = array();
            $this->_isFooter = count($data['footer']) > 0;
            if (isset($data['footer'])) {
                $nextRow++;
                $startRow = $nextRow;
                $footers = $data['footer'];
                foreach ($footers as $footer) {
                    $footerId = "FOOTER{$nextRow}_{$id}";
                    $colIndex = -1;
                    foreach ($footer as $k => $v) {
                        $colIndex++;
                        $tag = "{" . $footerId . ":" . $k . "}";
                        $this->objWorksheet->setCellValueByColumnAndRow($colIndex, $nextRow, $tag);
                        if (isset($config['footer'][$k]['align']))
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getAlignment()->setHorizontal($config['footer'][$k]['align']);

                        if (isset($config['footer'][$k]['borders'])) {
                            $arrBorders = $config['footer'][$k]['borders'];
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getLeft()->applyFromArray($arrBorders);
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getRight()->applyFromArray($arrBorders);
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getTop()->applyFromArray($arrBorders);
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->getBottom()->applyFromArray($arrBorders);

                            // Для правильного отображения границы последней строки в HTML отчете
                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow + 1)->getBorders()->getTop()->applyFromArray($arrBorders);
                        } else {
                            if ($this->_cellStyleArray['borders']) {
                                $arrBorders = $this->_cellStyleArray['borders'];
                                $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow)->getBorders()->applyFromArray($arrBorders);

                                // Для правильного отображения границы последней строки в HTML отчете
                                $arrBorders = $this->_cellStyleArray['borders']['bottom'];
                                $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow + 1)->getBorders()->getTop()->applyFromArray($arrBorders);
                            }
                        }
                    }
                    $nextRow++;
                    //add footer row to load collection
                    $loadCollection[] = array('id' => $footerId, 'data' => $footer, 'format' => $format);
                }

                if ($colIndex > -1) {
                    $this->objWorksheet->getStyle(PHPExcel_Cell::stringFromColumnIndex(0) . $startRow . ':' . PHPExcel_Cell::stringFromColumnIndex($colIndex) . ($nextRow - 1))->applyFromArray($this->_footerStyleArray);
                }
            } else {
                // Для правильного отображения границы последней строки в HTML отчете
//                $arrBorders = $this->_cellStyleArray['borders']['bottom'];
//                $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow + 1)->getBorders()->getTop()->applyFromArray($arrBorders);
            }

            $this->load($loadCollection);
            $this->generateReport();
        }
    }

    /**
     * Generates report based on loaded data 
     */
    public function generateReport() {
        // Установим свойства отчета
        $this->setReportProperties();

        // Установим свойства для печати страницы
        $this->setReportPageSetup();

        // Сформируем отчет
        foreach ($this->_data as $data) {
            if (isset($data['repeat']) && $data['repeat'] == true) {
                //Repeating data
                $foundTags = false;
                $repeatRange = '';
                $firstRow = '';
                $lastRow = '';

                $firstCol = 'A'; //TODO: better detection
                $lastCol = $this->objWorksheet->getHighestColumn(); //TODO: better detection
                //scan the template
                //search for repeating part
                foreach ($this->objWorksheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $rowIndex = $row->getRowIndex();
                    //find the repeating range (one or more rows)
                    foreach ($cellIterator as $cell) {
                        $cellval = trim($cell->getValue());
                        $column = $cell->getColumn();
                        //see if the cell has something for replacing
                        if (preg_match_all("/\{" . $data['id'] . ":(\w*|#\+?-?(\d*)?)\}/", $cellval, $matches)) {
                            //this cell has replacement tags
                            if (!$foundTags)
                                $foundTags = true;
                            //remember the first ant the last row
                            if ($rowIndex != $firstRow)
                                $lastRow = $rowIndex;
                            if ($firstRow == '')
                                $firstRow = $rowIndex;
                        }
                    }
                }

                //form the repeating range
                if ($foundTags)
                    $repeatRange = $firstCol . $firstRow . ":" . $lastCol . $lastRow;

                //set initial format data
                if (!isset($data['format']))
                    $data['format'] = array();

                //check if data is an array
                if (is_array($data['data'])) {
                    //every element is an array with data for all the columns
                    if ($foundTags) {
                        //insert repeating rows, as many as needed
                        //check if grouping is defined
                        if (count($this->_group)) {
                            $this->generateRepeatingRowsWithGrouping($data, $repeatRange);
                        } else {
                            $this->generateRepeatingRows($data, $repeatRange);
                        }
                        //remove the template rows
                        for ($i = $firstRow; $i <= $lastRow; $i++) {
                            $this->objWorksheet->removeRow($firstRow);
                        }
                        //if there is no data
                        if (count($data['data']) == 0)
                            $this->addNoResultRow($firstRow, $firstCol, $lastCol);
                    }
                }
                else {
                    //TODO
                    //maybe an SQL query?
                    //needs to be database agnostic
                }
            } else {
                //non-repeating data
                //check for additional formating
                if (!isset($data['format']))
                    $data['format'] = array();

                //check if data is an array or mybe a SQL query
                if (is_array($data['data'])) {
                    //array of data
                    $this->generateSingleRow($data);
                } else {
                    //TODO
                    //maybe an SQL query?
                    //needs to be database agnostic
                }
            }
        }

        //call the replacing function
        $this->searchAndReplace();

        //generate heading if heading text is set
        if ($this->_headingText && !$this->_renderHeading) {
            $this->generateHeading();
            $this->_renderHeading = true;
        }

        //generate footing if footing text is set
        if ($this->_footingText && !$this->_renderFooting) {
            $this->generateFooting();
            $this->_renderFooting = true;
        }

        // Прорисуем бордюр для правильного отображения в HTML
        if (!$this->_isFooter) {
            $highestRow = $this->objWorksheet->getHighestRow(); // e.g. 10
            $highestRow = $highestRow - 1;
            $highestColumn = $this->objWorksheet->getHighestColumn(); // e.g 'F'
            //Apply style
            $arrBorders = $this->_cellStyleArray['borders']['top'];
            $this->objWorksheet->getStyle("A{$highestRow}:{$highestColumn}{$highestRow}")->getBorders()->getTop()->applyFromArray($arrBorders);
        }
    }

    /**
     * Generates single non-repeating row of data
     * @param array $data 
     */
    private function generateSingleRow(& $data) {
        $id = $data['id'];
        $format = $data['format'];
        foreach ($data['data'] as $key => $value) {
            $search = "{" . $id . ":" . $key . "}";
            $this->_search[] = $search;

            //if it needs formating
            if (isset($format[$key])) {
                foreach ($format[$key] as $ftype => $f) {
                    $value = $this->formatValue($value, $ftype, $f);
                }
            }
            $this->_replace[] = $value;
        }
    }

    /**
     * Generates repeating rows of data with some template range
     * @param array $data
     * @param string $repeatRange 
     */
    private function generateRepeatingRows(& $data, $repeatRange) {
        $rowCounter = 0;
        $repeatTemplateArray = $this->objWorksheet->rangeToArray($repeatRange, null, true, true, true);
        //insert repeating rows but first check for minimum number of rows
        if (isset($data['minRows'])) {
            $minRows = (int) $data['minRows'];
        }
        else
            $minRows = 0;
        $templateKeys = array_keys($repeatTemplateArray);
        $lastRowFoundAt = end($templateKeys);
        $firstRowFoundAt = reset($templateKeys);
        $rowsFound = count($repeatTemplateArray);

        $mergeCells = $this->objWorksheet->getMergeCells();
        $needMerge = array();
        foreach ($mergeCells as $mergeCell) {
            if ($this->isSubrange($mergeCell, $repeatRange)) {
                //contains merged cells, save for later
                $needMerge[] = $mergeCell;
            }
        }
        //check all the data
        foreach ($data['data'] as $value) {
            $rowCounter++;
            $skip = $rowCounter * $rowsFound;
            $newRowIndex = $firstRowFoundAt + $skip;

            //insert one or more rows if needed
            if ($minRows < $rowCounter)
                $this->objWorksheet->insertNewRowBefore($newRowIndex, $rowsFound);

            //copy merge definitions
            foreach ($needMerge as $nm) {
                $nm = PHPExcel_Cell::rangeBoundaries($nm);
                $newMerge = PHPExcel_Cell::stringFromColumnIndex($nm[0][0] - 1) . ($nm[0][1] + $skip) . ":" . PHPExcel_Cell::stringFromColumnIndex($nm[1][0] - 1) . ($nm[1][1] + $skip);

                $this->objWorksheet->mergeCells($newMerge);
            }

            //generate row of data
            $this->generateSingleRepeatingRow($value, $repeatTemplateArray, $rowCounter, $skip, $data['id'], $data['format']);
        }
        //remove merge on template, BUG fix
        foreach ($needMerge as $nm) {
            $this->objWorksheet->unmergeCells($nm);
        }
    }

    /**
     * Generates repeating rows of data with some template range but also with grouping
     * @param array $data
     * @param string $repeatRange 
     */
    private function generateRepeatingRowsWithGrouping(& $data, $repeatRange) {
        $rowCounter = 0;
        $groupCounter = 0;
        $footerCount = 0;
        $repeatTemplateArray = $this->objWorksheet->rangeToArray($repeatRange, null, true, true, true);
        //insert repeating rows but first check for minimum number of rows
        if (isset($data['minRows'])) {
            $minRows = (int) $data['minRows'];
        }
        else
            $minRows = 0;

        $templateKeys = array_keys($repeatTemplateArray);
        $lastRowFoundAt = end($templateKeys);
        $firstRowFoundAt = reset($templateKeys);
        $rowsFound = count($repeatTemplateArray);

        list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($repeatRange);
        $firstCol = PHPExcel_Cell::stringFromColumnIndex($rangeStart[0] - 1);
        $lastCol = PHPExcel_Cell::stringFromColumnIndex($rangeEnd[0] - 1);

        $mergeCells = $this->objWorksheet->getMergeCells();
        $needMerge = array();
        foreach ($mergeCells as $mergeCell) {
            if ($this->isSubrange($mergeCell, $repeatRange)) {
                //contains merged cells, save for later
                $needMerge[] = $mergeCell;
            }
        }

        //group array should have header, rows and summary elements
        foreach ($this->_group['rows'] as $name => $rows) {
            $groupCounter++;
            $caption = $this->_group['caption'][$name];
            $newRowIndex = $firstRowFoundAt + $rowCounter * $rowsFound + $footerCount * $rowsFound + $groupCounter;
            //insert header for the group
            $this->objWorksheet->insertNewRowBefore($newRowIndex, 1);
            $this->objWorksheet->setCellValue($firstCol . $newRowIndex, $caption);
            $this->objWorksheet->mergeCells($firstCol . $newRowIndex . ":" . $lastCol . $newRowIndex);

            //add style for the header
            $this->objWorksheet->getStyle($firstCol . $newRowIndex)->applyFromArray($this->_headerGroupStyleArray);

            // Добавим бордюры для отдельных ячеек это нужно для (exel)
            $arrBorders = $this->_headerGroupStyleArray['borders']['top'];
            $arrCols = range($firstCol, $lastCol);
            $lastCol = end($arrCols);
            foreach ($arrCols as $col) {
                $this->objWorksheet->getStyle("{$col}{$newRowIndex}")->getBorders()->getTop()->applyFromArray($arrBorders);
                $this->objWorksheet->getStyle("{$col}{$newRowIndex}")->getBorders()->getBottom()->applyFromArray($arrBorders);
                if ($lastCol == $col) {
                    $this->objWorksheet->getStyle("{$col}{$newRowIndex}")->getBorders()->getRight()->applyFromArray($arrBorders);
                }
            }

            //add data for the group
            foreach ($rows as $row) {
                $value = $data['data'][$row];
                $rowCounter++;
                $skip = $rowCounter * $rowsFound + $footerCount * $rowsFound + $groupCounter;
                $newRowIndex = $firstRowFoundAt + $skip;

                //insert one or more rows if needed
                if ($minRows < $rowCounter)
                    $this->objWorksheet->insertNewRowBefore($newRowIndex, $rowsFound);

                //copy merge definitions
                foreach ($needMerge as $nm) {
                    $nm = PHPExcel_Cell::rangeBoundaries($nm);
                    $newMerge = PHPExcel_Cell::stringFromColumnIndex($nm[0][0] - 1) . ($nm[0][1] + $skip) . ":" . PHPExcel_Cell::stringFromColumnIndex($nm[1][0] - 1) . ($nm[1][1] + $skip);

                    $this->objWorksheet->mergeCells($newMerge);
                }

                //generate row of data
                $this->generateSingleRepeatingRow($value, $repeatTemplateArray, $rowCounter, $skip, $data['id'], $data['format']);
            }

            //include the footer if defined
            if (isset($this->_group['summary']) && isset($this->_group['summary'][$name])) {
                $footerCount++;
                $skip = $groupCounter + $rowCounter * $rowsFound + $footerCount * $rowsFound;
                $newRowIndex = $firstRowFoundAt + $skip;

                $this->objWorksheet->insertNewRowBefore($newRowIndex, $rowsFound);
                $this->generateSingleRepeatingRow($this->_group['summary'][$name], $repeatTemplateArray, '', $skip, $data['id'], $data['format']);
                //add style for the footer

                $this->objWorksheet->getStyle($firstCol . $newRowIndex . ":" . $lastCol . $newRowIndex)->applyFromArray($this->_footerGroupStyleArray);
            }

            //remove merge on template, BUG fix
            foreach ($needMerge as $nm) {
                $this->objWorksheet->unmergeCells($nm);
            }
        }
    }

    /**
     * Generates single row for repeating data
     * @param array $value
     * @param array $repeatTemplateArray
     * @param int $rowCounter
     * @param int $skip
     * @param string $id
     * @param array $format 
     */
    private function generateSingleRepeatingRow(& $value, & $repeatTemplateArray, $rowCounter, $skip, $id, $format) {
        $formatValue = "";
        foreach ($repeatTemplateArray as $rowKey => $rowData) {
            foreach ($rowData as $col => $tag) {
                //$col is like A, B, C, ...
                //$rowKey is like 9,10,11, ...
                //$tag can have many replacement tags, e.g. "{item:item_id} --- {item:item_code}"

                if (preg_match_all("/\{" . $id . ":(\w*|#\+?-?(\d*)?)\}/", $tag, $matches)) {
                    $matchTags = $matches[0]; //array with complete tags, e.g. '{item:item_id}'
                    $matchKeys = $matches[1]; //array with only the key names, e.g. 'item_id'
                    $matchNumber = count($matchTags); //how many replacement tags is there in this cell
                    $replaceTags = array();
                    $replaceValues = array();
                    foreach ($matchKeys as $mkey) {
                        $formatValue = "";
                        $replaceTags[] = "{" . $id . ":" . $mkey . "}";
                        if (strpos($mkey, "#") === 0) {
                            //this is a counter (optional offset)
                            $offset = explode("+", $mkey);
                            if (count($offset) > 1)
                                $offset = $offset[1];
                            else
                                $offset = 0;

                            $rValue = $rowCounter + (int) $offset;
                        }
                        elseif (key_exists($mkey, $value)) {
                            //format if needed
                            if (isset($format) && isset($format[$mkey])) {
                                foreach ($format[$mkey] as $ftype => $f) {
                                    $rValue = $this->formatValue($value[$mkey], $ftype, $f);
                                    if ($ftype == "number") {
                                        $formatValue = $this->getFormatNumber($f);
                                    }
                                }
                            } else {
                                //without additional formating
                                $rValue = $value[$mkey];
                            }
                        } else {
                            $rValue = $mkey;
                        }


                        //add to replace array
                        $replaceValues[] = $rValue;
                    }
                    //replace all the values in this cell
                    $tag = str_replace($replaceTags, $replaceValues, $tag);
                }
                $newCellAddress = $col . ($rowKey + $skip);
                $this->objWorksheet->setCellValue($newCellAddress, $tag);

                //copy cell styles
                $xfIndex = $this->objWorksheet->getCell($col . $rowKey)->getXfIndex();
                $this->objWorksheet->getCell($newCellAddress)->setXfIndex($xfIndex);

                // Установим формат ячейки
                if ($formatValue) {
                    $this->objWorksheet->getStyle($newCellAddress)->getNumberFormat()->setFormatCode($formatValue);
                }

                //strip rows if requested
                if ($this->_useStripRows && $rowCounter % 2) {
                    $arrFill = $this->_cellStyleArray['fill'];
                    $this->objWorksheet->getStyle($newCellAddress)->getFill()->applyFromArray($arrFill);
                }

                // Установим фонт для данных
                $arrFont = $this->_cellStyleArray['font'];
                $this->objWorksheet->getStyle($newCellAddress)->getFont()->applyFromArray($arrFont);
            }
        }
    }

    /**
     * Replaces all the cells with real data
     */
    private function searchAndReplace() {
        $numberFormats = array();
        //---------------------------
        foreach ($this->objWorksheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            foreach ($cellIterator as $cell) {
                $cellValue = $cell->getValue();
                $value = str_replace($this->_search, $this->_replace, $cellValue);

                if (is_numeric($value)) {

                    // Получим числовой формат ячейки
                    $column = $cell->getColumn();
                    $row = $cell->getRow();
                    $numberFormat = $this->objWorksheet->getStyle("{$column}{$row}")->getNumberFormat()->getFormatCode();

                    // Установим новый формат числа в соответствии с предыдущим форматом числа
                    // это используется в основном для данных "footer", 
                    // а предыдущий формат берется из данных "data"
                    if (array_key_exists($column, $numberFormats)) {
                        $numberFormatPrev = $numberFormats[$column];
                        if ($numberFormat == PHPExcel_Style_NumberFormat::FORMAT_GENERAL) {
                            $numberFormat = $numberFormatPrev;
                            $this->objWorksheet->getStyle("{$column}{$row}")->getNumberFormat()->setFormatCode($numberFormat);
                        } else {
                            $numberFormats[$column] = $numberFormat;
                        }
                    } else {
                        $numberFormats[$column] = $numberFormat;
                    }
                }
                $cell->setValue($value);
            }
        }
    }

    /**
     * Adda a row for repeating data when there is no results
     * @param int $rowIndex
     * @param string $colMin
     * @param string $colMax 
     */
    private function addNoResultRow($rowIndex, $colMin, $colMax) {
        //insert one row
        $this->objWorksheet->insertNewRowBefore($rowIndex);

        //merge as required
        $this->objWorksheet->mergeCells($colMin . $rowIndex . ":" . $colMax . $rowIndex);

        //insert text

        $this->objWorksheet->setCellValue($colMin . $rowIndex, $this->_noResultText);

        $this->objWorksheet->getStyle($colMin . $rowIndex . ":" . $colMax . $rowIndex)->applyFromArray($this->_noResultStyleArray);
    }

    /**
     * Generates heading title of the report
     */
    private function generateHeading() {
        //get current dimensions
        $highestRow = $this->objWorksheet->getHighestRow(); // e.g. 10
        $highestColumn = $this->objWorksheet->getHighestColumn(); // e.g 'F'

        $insertCount = 1;

        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

        //insert row on top
        $this->objWorksheet->insertNewRowBefore(1, $insertCount);

        //merge cells
        $this->objWorksheet->mergeCells("A1:" . $highestColumn . "1");

        //set the text for header
        $this->objWorksheet->setCellValue("A1", $this->_headingText);
        $this->objWorksheet->getStyle('A1')->getAlignment()->setWrapText(true);
        $this->objWorksheet->getRowDimension('1')->setRowHeight(48);

        // Установим нижний бардюр для вставляемой строчки,
        // для правильного отображения в HTML
        if ($this->_headerStyleArray["borders"]) {
            $arrBorders = $this->_headerStyleArray["borders"]["bottom"];
            for ($indexColumn = 0; $indexColumn < $highestColumnIndex; $indexColumn++) {
                $this->objWorksheet->getStyleByColumnAndRow($indexColumn, $insertCount)->getBorders()->getBottom()->applyFromArray($arrBorders);
            }
        }


        //Apply style
        $this->objWorksheet->getStyle("A1")->applyFromArray($this->_headingStyleArray);
    }

    /**
     * Generates footing title of the report
     */
    private function generateFooting() {
        //get current dimensions
        $highestRow = $this->objWorksheet->getHighestRow(); // e.g. 10
//        $highestRow--;
        $highestColumn = $this->objWorksheet->getHighestColumn(); // e.g 'F'

        $highestColumnIndex = PHPExcel_Cell::columnIndexFromString($highestColumn);

        //merge cells
        $this->objWorksheet->mergeCells("A{$highestRow}:" . $highestColumn . "$highestRow");

        //set the text for footer
        $this->objWorksheet->setCellValue("A{$highestRow}", $this->_footingText);
        $this->objWorksheet->getStyle("A{$highestRow}")->getAlignment()->setWrapText(true);
        $this->objWorksheet->getRowDimension('1')->setRowHeight(20);

        //Apply style
        $this->objWorksheet->getStyle("A{$highestRow}")->applyFromArray($this->_footingStyleArray);
    }

    /**
     * Set merge of cells
     * 
     * @param array $arrMergeCells
     * 
     */
    private function setMergeCells($arrMergeCells) {
        // Сделаем обьединение ячеек
        if (count($arrMergeCells) > 0) {

            foreach ($arrMergeCells as $tag => $arrColumnsRows) {
                if (count($arrColumnsRows) > 1) {
                    $index1 = 0;
                    $index2 = count($arrColumnsRows) - 1;
                    $this->objWorksheet->mergeCellsByColumnAndRow(
                            $arrColumnsRows[$index1][0], $arrColumnsRows[$index1][1], $arrColumnsRows[$index2][0], $arrColumnsRows[$index2][1]);
                }
            }
        }
    }

    //// Для правильного отображения границы нижней строки header в HTML отчете
//                            $arrBorders = $this->_headerStyleArray['borders']['bottom'];
//                            $this->objWorksheet->getStyleByColumnAndRow($colIndex, $nextRow-1)->getBorders()->getBottom()->applyFromArray($arrBorders);

    /**
     * Set merge of cells
     * 
     * @param array $arrMergeCells
     * 
     */
    private function setMergeCellsBorders($arrMergeCells) {
        // Сделаем обьединение ячеек
        if (count($arrMergeCells) > 0) {

            foreach ($arrMergeCells as $tag => $arrColumnsRows) {
                if (count($arrColumnsRows) > 1) {
                    $index1 = 0;
                    $index2 = count($arrColumnsRows) - 1;

                    // Рисуем бордюр вокруг первой ячейки в группе ячеек (для HTML)
                    $arrBorders = $this->_headerStyleArray['borders'];
                    $this->objWorksheet->getStyleByColumnAndRow($arrColumnsRows[$index1][0], $arrColumnsRows[$index1][1])->getBorders()->applyFromArray($arrBorders);

                    // Рисуем бордюр справа для всех ячеек, входящих в группу (для excel)
                    $arrBorders = $this->_headerStyleArray['borders']['right'];
                    foreach ($arrColumnsRows as $row) {
                        $this->objWorksheet->getStyleByColumnAndRow($row[0], $row[1])->getBorders()->getRight()->applyFromArray($arrBorders);
                    }
                }
            }
        }
    }

    //==== FORMAT OF DATA ========//

    /**
     * Get code formating for number
     */

    /**
     * 
     * Type can be number
     * @param array $f // Data for formating
     * 
     * @return string 
     */
    protected function getFormatNumber($f) {
        $formatValue = "";
        //"#,##0.00" '"$"#,##0.00_-'
        //----------------------
        $prefix = $f["prefix"];
        $sufix = $f["sufix"];
        $decPoint = $f["decPoint"];
        if (!$decPoint) {
            $decPoint = "";
        }
        $thousandsSep = $f["thousandsSep"];
        if (!$thousandsSep) {
            $thousandsSep = "";
        }
        $decimals = (int) $f["decimals"];
        if ($decimals) {
            $str_decimals = str_pad("", $decimals, "0");
        } else {
            $str_decimals = "";
        }

        if (!$decPoint || !$str_decimals) {
            if ($thousandsSep) {
                $formatValue = "{$prefix}" . "#{$thousandsSep}##0" . " {$sufix}";
            } else {
                $formatValue = '0';
            }
        } else {
            if ($thousandsSep) {
                $formatValue = "{$prefix}" . "#{$thousandsSep}##0{$decPoint}{$str_decimals}" . " {$sufix}";
            } else {
                $formatValue = "{$prefix}" . "0{$decPoint}{$str_decimals}" . " {$sufix}";
            }
        }

        $formatValue = trim($formatValue);

        return $formatValue;
    }

    /**
     * Check and apply various formating
     */

    /**
     * Applies various formatings
     * Type can be datetime or number
     * @param mixed $value
     * @param string $type
     * @param mixed $format 
     */
    protected function formatValue($value, $type, $format) {//setFormatCode
        if ($type == 'datetime') {
            //format can only be string
            if (is_string($format) && strtotime($value) > 0)
                $value = date($format, strtotime($value));
        }
        elseif ($type == 'number') {
            //format must be an array
            if (is_array($format)) {
                //set the defaults
                if (!isset($format['prefix']))
                    $format['prefix'] = '';
                if (!isset($format['decimals']))
                    $format['decimals'] = 0;
                if (!isset($format['decPoint']))
                    $format['decPoint'] = '.';
                if (!isset($format['thousandsSep']))
                    $format['thousandsSep'] = ',';
                if (!isset($format['sufix']))
                    $format['sufix'] = '';
                $value = $format['prefix'] . number_format($value, $format['decimals'], $format['decPoint'], $format['thousandsSep']) . $format['sufix'];
            }
        }

        return $value;
    }

    //==== RENDER OF REPORT ========//

    /**
     * Renders report as specified output file
     * @param string $type
     * @param string $filename 
     */
    public function render($type = 'html-page', $filename = '') {
        //create or generate report
        if ($this->_usingTemplate) {
            $this->generateReport();
        } else {
            // Отчет создается первый раз
            if (count($this->_data) == 1) {
                $this->createReport();
            } else {// Отчет создается повторно
                $this->generateReport();
            }
        }

        if ($type == '')
            $type = "html-page";

        if ($filename == '') {
            $filename = "Report " . date("Y-m-d");
        }

        if (strtolower($type) == 'html-page')
            return $this->renderHtml();
        elseif (strtolower($type) == 'html-table')
            return $this->renderHtml(false);
        elseif (strtolower($type) == 'excel2007')
            return $this->renderXlsx($filename);
        elseif (strtolower($type) == 'excel2003')
            return $this->renderXls($filename);
        elseif (strtolower($type) == 'tcpdf')
            return $this->renderTCPdf($filename);
        elseif (strtolower($type) == 'mpdf')
            return $this->renderMPdf($filename);
        else
            return "Error: unsupported export type!"; //TODO: better error handling
    }

    /**
     * Renders report as a HTML output
     * 
     * @param string $isFullHtml Полный файл HTML
     * @return string
     * 
     */
    private function renderHtml($isFullHtml = true) {

        if ($isFullHtml) {
            $this->objWriter = new PHPExcel_Writer_HTML($this->objPHPExcel);
//            $this->objWriter->setUseInlineCss(true);
//            // Build CSS
//            $this->objWriter->buildCSS(true);
            // Generate HTML  
            $html = '';
            $html .= $this->objWriter->generateHTMLHeader(false);
            $html .= '<style type="text/css">' . PHP_EOL;
            $html .= '	  ' . 'html { font-family:Calibri, Arial, Helvetica, sans-serif; font-size:11pt; background-color:white }' . PHP_EOL;
            $html .= $this->objWriter->generateStyles(false);
            $html .= '	  ' . ' table { page-break-after:avoid }' . PHP_EOL;
            $html .= '	  ' . ' td { padding-right: 2px;}' . PHP_EOL;
            $html .= '</style>' . PHP_EOL;
            $html .= $this->objWriter->generateSheetData();
            $html .= $this->objWriter->generateHTMLFooter();
            $html .= '';
        } else {
            $this->objWriter = new PHPExcel_MyWriter_HTML($this->objPHPExcel);

            // Generate HTML
            $divIsolated = 'div.php-excel';
            $html = '<style type="text/css">' . PHP_EOL;
            $html .= $this->generateIsolatedStyles($divIsolated);
            $html .= '	  ' . $divIsolated . ' table { page-break-after:avoid }' . PHP_EOL;
            $html .= '	  ' . $divIsolated . ' td { padding-right: 2px;}' . PHP_EOL;
            $html .= '</style>' . PHP_EOL;
            $html .= '<div class="php-excel">' . PHP_EOL;
            $html .= $this->objWriter->generateSheetData() . PHP_EOL;
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Renders report as a XLSX file
     */
    private function renderXlsx($filename) {

        $this->objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');

        $path_parts = pathinfo($filename);
        $dirname = $path_parts['dirname'];
        if ($dirname == ".") {
            $dirname = "";
        }
        $extension = $path_parts['extension'];
        if (!$extension) {
            $filename .= ".xlsx";
        }
        if ($dirname && is_dir($dirname)) {
            $this->objWriter->save($filename);
            return $filename;
        } else {
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename);
            header('Cache-Control: max-age=0');

            $this->objWriter->save('php://output');
        }
    }

    /**
     * Renders report as a XLS file
     */
    private function renderXls($filename) {

        $this->objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel5');

        $path_parts = pathinfo($filename);
        $dirname = $path_parts['dirname'];
        if ($dirname == ".") {
            $dirname = "";
        }
        $extension = $path_parts['extension'];
        if (!$extension) {
            $filename .= ".xls";
        }
        if ($dirname && is_dir($dirname)) {
            $this->objWriter->save($filename);
            return $filename;
        } else {
            header('Content-Type: application/vnd.ms-excel');
            header('Content-Disposition: attachment;filename="' . $filename);
            header('Cache-Control: max-age=0');

            $this->objWriter->save('php://output');
        }
    }

    /**
     * Renders report as a PDF file
     * TCPDF library is used
     * 
     * @param string $filename
     */
    private function renderTCPdf($filename) {

        $this->objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'PDF');

        $path_parts = pathinfo($filename);
        $dirname = $path_parts['dirname'];
        if ($dirname == ".") {
            $dirname = "";
        }
        $extension = $path_parts['extension'];
        if (!$extension) {
            $filename .= ".pdf";
        }
        if ($dirname && is_dir($dirname)) {
            $this->objWriter->save($filename);
            return $filename;
        } else {
            header('Content-Type: application/vnd.pdf');
            header('Content-Disposition: attachment;filename="' . $filename);
            header('Cache-Control: max-age=0');

            $this->objWriter->save('php://output');
        }
    }

    /**
     * Renders report as a PDF file
     * mPDF library is used
     * 
     * 
     * @param string $filename
     */
    private function renderMPdf($filename) {
        $pdfParams = array();
        //------------------
        $this->objWriter = new PHPExcel_Writer_mPDF($this->objPHPExcel);


        $path_parts = pathinfo($filename);
        $dirname = $path_parts['dirname'];
        if ($dirname == ".") {
            $dirname = "";
        }
        $extension = $path_parts['extension'];
        if (!$extension) {
            $filename .= ".pdf";
        }

        // Установим параметры для отчета
        $pdfParams['html'] = '';
        $pdfParams['isCommonFont'] = false;//(TRUE только для английского языка и др. основных, кроме русского)
        $pdfParams['pathStylesheet'] = '';
        $pdfParams['headerLeftMargin'] = $this->getConfig('headerPageTitle');
        $pdfParams['headerCentreMargin'] = $this->getConfig('headerCentreMargin');
        $pdfParams['footerRightMargin'] = $this->getConfig('footerRightMargin');

        if ($dirname && is_dir($dirname)) {
            $pdfParams['pdfReport'] = $filename;
            $this->objWriter->save($filename, $pdfParams);
            return $filename;
        } else {
            header('Content-Type: application/vnd.pdf');
            header('Content-Disposition: attachment;filename="' . $filename);
            header('Cache-Control: max-age=0');

            $this->objWriter->save('php://output', $pdfParams);
        }
    }

    //=============== FUNCTIONS ============//

    /**
     * Helper function for checking subranges of a range
     */
    public function isSubrange($subRange, $range) {
        list($rangeStart, $rangeEnd) = PHPExcel_Cell::rangeBoundaries($range);
        list($subrangeStart, $subrangeEnd) = PHPExcel_Cell::rangeBoundaries($subRange);
        return (($subrangeStart[0] >= $rangeStart[0]) && ($subrangeStart[1] >= $rangeStart[1]) && ($subrangeEnd[0] <= $rangeEnd[0]) && ($subrangeEnd[1] <= $rangeEnd[1]));
    }

    /**
     * Enabling strip rows
     */
    function enableStripRows() {
        $this->_useStripRows = true;
    }

    /**
     * Sets title of the report header
     */
    function setHeading($h) {
        $this->_headingText = $h;
    }

    /**
     * Sets title of the report footer
     */
    function setFooting($h) {
        $this->_footingText = $h;
    }

    /**
     * converts pixels to excel units
     * @param float $p
     * @return float 
     */
    function pixel2unit($p) {
        return ($p - 5) / 7;
    }

    /**
     * Generate Isolated CSS styles
     *
     * @param	string	$divIsolated	Generate isolated DIV (   <style> div.isolated table{..} div.isolated .gridlines td{..} .. </style> )
     * @return	string
     * @throws	Exception
     */
    function generateIsolatedStyles($divIsolated) {

        // Build Styles
        $html = $this->objWriter->generateStyles(false);

        $arrStyle = explode(PHP_EOL, $html);
        $html = '';
        foreach ($arrStyle as $style) {
            $html .= '	  ' . $divIsolated . " " . trim($style) . PHP_EOL;
        }
        return $html;
    }

    /**
     * Получить значение ячейки
     * 
     * Адресовать ячейки при работе с excel можно разными способами: 
     *   - колонка и ряд в виде строки: «A1»
     *   - колонка буквой, ряд числом: («A», 1)
     *   - колонка и ряд числом: (1, 1)
     * 
     * @param type $cellOrCol
     * @param type $row
     * @return type 
     */
    public function getCellValue($cellOrCol, $row = null, $format = 'd.m.Y') {
        //column set by index
        if (is_numeric($cellOrCol)) {
            $cell = $this->objWorksheet->getCellByColumnAndRow($cellOrCol, $row);
        } else {
            $lastChar = substr($cellOrCol, -1, 1);
            if (!is_numeric($lastChar)) { //column contains only letter, e.g. "A"
                $cellOrCol .= $row;
            }

            $cell = $this->objWorksheet->getCell($cellOrCol);
        }

        // Определим ячейку если она попадает в диапазон обьединенных ячеек
        $mergedCellsRange = $this->objWorksheet->getMergeCells();
        foreach ($mergedCellsRange as $currMergedRange) {
            if ($cell->isInRange($currMergedRange)) {
                $currMergedCellsArray = PHPExcel_Cell::splitRange($currMergedRange);
                $cell = $this->objWorksheet->getCell($currMergedCellsArray[0][0]);
                break;
            }
        }

        // Получим значение из ячейки
        $val = $cell->getValue();

        //date
        if (PHPExcel_Shared_Date::isDateTime($cell)) {
            $val = date($format, PHPExcel_Shared_Date::ExcelToPHP($val));
        }

        //for incorrect formulas take old value
        if ((substr($val, 0, 1) === '=' ) && (strlen($val) > 1)) {
            $val = $cell->getOldCalculatedValue();
        }


        return $val;
    }

}

/**
 * PHPExcel_Writer_mPDF
 *
 * @category	PHPExcel
 * @package	PHPExcel_Writer
 * @copyright	Copyright (c) 2006 - 2012 PHPExcel (http://www.codeplex.com/PHPExcel)
 */
class PHPExcel_Writer_mPDF extends PHPExcel_Writer_PDF {

    /**
     * Paper Sizes xRef List
     *
     * @var array
     */
    private static $_paperSizes = array(
        //	Excel Paper Size													TCPDF Paper Size
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER => 'LETTER', //	(8.5 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_SMALL => 'LETTER', //	(8.5 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_TABLOID => array(792.00, 1224.00), //	(11 in. by 17 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEDGER => array(1224.00, 792.00), //	(17 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEGAL => 'LEGAL', //	(8.5 in. by 14 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_STATEMENT => array(396.00, 612.00), //	(5.5 in. by 8.5 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_EXECUTIVE => 'EXECUTIVE', //	(7.25 in. by 10.5 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3 => 'A3', //	(297 mm by 420 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4 => 'A4', //	(210 mm by 297 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_SMALL => 'A4', //	(210 mm by 297 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A5 => 'A5', //	(148 mm by 210 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_B4 => 'B4', //	(250 mm by 353 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_B5 => 'B5', //	(176 mm by 250 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_FOLIO => 'FOLIO', //	(8.5 in. by 13 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_QUARTO => array(609.45, 779.53), //	(215 mm by 275 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_1 => array(720.00, 1008.00), //	(10 in. by 14 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_2 => array(792.00, 1224.00), //	(11 in. by 17 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_NOTE => 'LETTER', //	(8.5 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO9_ENVELOPE => array(279.00, 639.00), //	(3.875 in. by 8.875 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO10_ENVELOPE => array(297.00, 684.00), //	(4.125 in. by 9.5 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO11_ENVELOPE => array(324.00, 747.00), //	(4.5 in. by 10.375 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO12_ENVELOPE => array(342.00, 792.00), //	(4.75 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_NO14_ENVELOPE => array(360.00, 828.00), //	(5 in. by 11.5 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_C => array(1224.00, 1584.00), //	(17 in. by 22 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_D => array(1584.00, 2448.00), //	(22 in. by 34 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_E => array(2448.00, 3168.00), //	(34 in. by 44 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_DL_ENVELOPE => array(311.81, 623.62), //	(110 mm by 220 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_C5_ENVELOPE => 'C5', //	(162 mm by 229 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_C3_ENVELOPE => 'C3', //	(324 mm by 458 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_C4_ENVELOPE => 'C4', //	(229 mm by 324 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_C6_ENVELOPE => 'C6', //	(114 mm by 162 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_C65_ENVELOPE => array(323.15, 649.13), //	(114 mm by 229 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_B4_ENVELOPE => 'B4', //	(250 mm by 353 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_B5_ENVELOPE => 'B5', //	(176 mm by 250 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_B6_ENVELOPE => array(498.90, 354.33), //	(176 mm by 125 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_ITALY_ENVELOPE => array(311.81, 651.97), //	(110 mm by 230 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_MONARCH_ENVELOPE => array(279.00, 540.00), //	(3.875 in. by 7.5 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_6_3_4_ENVELOPE => array(261.00, 468.00), //	(3.625 in. by 6.5 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_US_STANDARD_FANFOLD => array(1071.00, 792.00), //	(14.875 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_GERMAN_STANDARD_FANFOLD => array(612.00, 864.00), //	(8.5 in. by 12 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_GERMAN_LEGAL_FANFOLD => 'FOLIO', //	(8.5 in. by 13 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_ISO_B4 => 'B4', //	(250 mm by 353 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_JAPANESE_DOUBLE_POSTCARD => array(566.93, 419.53), //	(200 mm by 148 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_PAPER_1 => array(648.00, 792.00), //	(9 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_PAPER_2 => array(720.00, 792.00), //	(10 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_STANDARD_PAPER_3 => array(1080.00, 792.00), //	(15 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_INVITE_ENVELOPE => array(623.62, 623.62), //	(220 mm by 220 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_EXTRA_PAPER => array(667.80, 864.00), //	(9.275 in. by 12 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LEGAL_EXTRA_PAPER => array(667.80, 1080.00), //	(9.275 in. by 15 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_TABLOID_EXTRA_PAPER => array(841.68, 1296.00), //	(11.69 in. by 18 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_EXTRA_PAPER => array(668.98, 912.76), //	(236 mm by 322 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_TRANSVERSE_PAPER => array(595.80, 792.00), //	(8.275 in. by 11 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_TRANSVERSE_PAPER => 'A4', //	(210 mm by 297 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_EXTRA_TRANSVERSE_PAPER => array(667.80, 864.00), //	(9.275 in. by 12 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_SUPERA_SUPERA_A4_PAPER => array(643.46, 1009.13), //	(227 mm by 356 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_SUPERB_SUPERB_A3_PAPER => array(864.57, 1380.47), //	(305 mm by 487 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_LETTER_PLUS_PAPER => array(612.00, 913.68), //	(8.5 in. by 12.69 in.)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A4_PLUS_PAPER => array(595.28, 935.43), //	(210 mm by 330 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A5_TRANSVERSE_PAPER => 'A5', //	(148 mm by 210 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_JIS_B5_TRANSVERSE_PAPER => array(515.91, 728.50), //	(182 mm by 257 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3_EXTRA_PAPER => array(912.76, 1261.42), //	(322 mm by 445 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A5_EXTRA_PAPER => array(493.23, 666.14), //	(174 mm by 235 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_ISO_B5_EXTRA_PAPER => array(569.76, 782.36), //	(201 mm by 276 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A2_PAPER => 'A2', //	(420 mm by 594 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3_TRANSVERSE_PAPER => 'A3', //	(297 mm by 420 mm)
        PHPExcel_Worksheet_PageSetup::PAPERSIZE_A3_EXTRA_TRANSVERSE_PAPER => array(912.76, 1261.42) //	(322 mm by 445 mm)
    );

    /**
     * Create a new PHPExcel_Writer_PDF
     *
     * @param 	PHPExcel	$phpExcel	PHPExcel object
     */
    public function __construct(PHPExcel $phpExcel) {
        parent::__construct($phpExcel);
    }

    /**
     * Save PHPExcel to file
     *
     * @param 	string 		$pFilename    Filename for the saved file
     * @throws 	Exception
     */
    public function save($pFilename = null, $params = array()) {
        // garbage collect
        $this->_phpExcel->garbageCollect();

        $saveArrayReturnType = PHPExcel_Calculation::getArrayReturnType();
        PHPExcel_Calculation::setArrayReturnType(PHPExcel_Calculation::RETURN_ARRAY_AS_VALUE);

        // Open file
        if ($pFilename == 'php://output') {
            $pFilename = "";
        }


        // Set PDF
        $this->_isPdf = true;

        // Build CSS
        $this->buildCSS(true);

        // Generate HTML
        $html = '';
        //$html .= $this->generateHTMLHeader(false);
        $html .= $this->generateSheetData();
        //$html .= $this->generateHTMLFooter();
        // Default PDF paper size
        $paperSize = 'A4'; //	Letter	(8.5 in. by 11 in.)
        // Check for paper size and page orientation
        if (is_null($this->getSheetIndex())) {
            $orientation = ($this->_phpExcel->getSheet(0)->getPageSetup()->getOrientation() == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE) ? 'L' : 'P';
            $printPaperSize = $this->_phpExcel->getSheet(0)->getPageSetup()->getPaperSize();
            $printMargins = $this->_phpExcel->getSheet(0)->getPageMargins();
        } else {
            $orientation = ($this->_phpExcel->getSheet($this->getSheetIndex())->getPageSetup()->getOrientation() == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE) ? 'L' : 'P';
            $printPaperSize = $this->_phpExcel->getSheet($this->getSheetIndex())->getPageSetup()->getPaperSize();
            $printMargins = $this->_phpExcel->getSheet($this->getSheetIndex())->getPageMargins();
        }

        // Override Page Orientation
        if (!is_null($this->_orientation)) {
            $orientation = ($this->_orientation == PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE) ? 'L' : 'P';
        }
        // Override Paper Size
        if (!is_null($this->_paperSize)) {
            $printPaperSize = $this->_paperSize;
        }


        if (isset(self::$_paperSizes[$printPaperSize])) {
            $paperSize = self::$_paperSizes[$printPaperSize];
        }

        if ($orientation == 'L') {
            $paperSize .= '-' . $orientation;
        }

        // Удалим название фонта
//        $html = str_replace("font-family:'Calibri';", "", $html);
        // Удалим перевод строки
        $html = str_replace("page-break-after:always", "", $html);

        // Установим параметры для отчета
        $params['pdfReport'] = $pFilename;
        $params['html'] = $html;
        $params['pageFormat'] = $paperSize;
        
        // Create PDF
        $this->_generateFilePDF($params);

        PHPExcel_Calculation::setArrayReturnType($saveArrayReturnType);
    }

    /*
     * Generate PDF файл
     *
     * @param array $params - параметры для преобразования HTML -> PDF
     *   - pdfReport (имя отчета);
     *   - isCommonFont (признак использования стандартных фонтов);
     *   - html (html строка);
     *   - pathStylesheet (путь к файлу CSS)
     * @return void
     */

    private function _generateFilePDF($params = array()) {

        //-----------------------------
        if (!$params['html']) {
            Default_Plugin_StrBox::errUser('ERR_CREATE_PDF_REPORT');
        }

        $mode = '';

        // Получим URLLogoReport
//        $config = Zend_Registry::get('config');
//        $urlLogoReport = $config['user']['main']['logo_report'];
//        $urlLogoReport = self::getFullURL_Res($urlLogoReport);
        //------------------------------------------------------
        // Установим значения по умолчанию
        $defaults = array(
            'pdfReport' => '', // Путь к файлу
            'html' => '',
            'isCommonFont' => FALSE,//(TRUE только для английского языка и др. основных, кроме русского)
            'pathStylesheet' => '',
            'isHeaders' => TRUE,
            'isFooters' => TRUE,
            'mirrorMargins' => TRUE,
            'headerLeftMargin' => '',
            'headerCentreMargin' => '',
            'headerRightMargin' => '{PAGENO}/{nbpg}',
            'footerLeftMargin' => '{DATE Y-m-j}',
            'footerCentreMargin' => '',
            'footerRightMargin' => '',
            'pageFormat' => 'A4', //Возможные форматы: пр. A3, A3-L, A4, A4-L ...
        );

        // Обьединим два массива
        $params = array_merge($defaults, $params);

        try {
            // Изменим параметры PHP 
            Default_Plugin_SysBox::iniSetConfig_PHP(array(
                "memory_limit" => "500M", //"256M",
                "max_execution_time" => "300"//"240"
            ));

            require_once("mpdf.php");

            if ($params['isCommonFont']) {
                $mode = 'c';
            }

            $isHeaders = (bool) $params['isHeaders'];
            $isFooters = (bool) $params['isFooters'];

            if ($isHeaders || $isFooters) {
                $mpdf = new mPDF($mode, $params['pageFormat'], '', '', 15, 15, 32, 20, 10, 10);
            } else {
                $mpdf = new mPDF($mode, $params['pageFormat']);
            }
            

            // Установим параметры для оптимизации (уменьшим время испольнения и используемую память)
            $mpdf->useOnlyCoreFonts = true;
            $mpdf->useSubstitutions = false;
            $mpdf->simpleTables = true; // Уменьшает время выполнения
            $mpdf->packTableData = true; // Уменьшает используемую память
            $mpdf->use_kwt = true; //Keep-with-table  Оставить заголовок таблицы вместе с маблицей на одной странице
//            $mpdf->shrink_tables_to_fit=0;
//            $mpdf->hyphenate = true;
//            $mpdf->SHYlang = 'ru';
//            $mpdf->SHYleftmin = 3;
            // Определим заголовок страницы
            $header = " 
            <table width=\"100%\" style=\"border-bottom: 1px solid #000000; vertical-align: bottom;  font-weight: bold; font-size: 14pt; color: #000088;\"><tr>
            <td width=\"33%\"><span style=\"\">{$params['headerLeftMargin']}</span></td>
            <td width=\"33%\" align=\"center\"><img src=\"{$params['headerCentreMargin']}\" /></td>
            <td width=\"33%\" style=\"text-align: right;\"><span style=\"\">{$params['headerRightMargin']}</span></td>
            </tr></table>
            ";
            // Определим подвал страницы
            $footer = "
            <table width=\"100%\" style=\"vertical-align: bottom;  font-size: 14pt; color: #000088; font-weight: bold; font-style: italic;\"><tr>
            <td width=\"33%\"><span style=\"\">{$params['footerLeftMargin']}</span></td>
            <td width=\"33%\" align=\"center\" style=\"\">{$params['footerCentreMargin']}</td>
            <td width=\"33%\" style=\"text-align: right; \">{$params['footerRightMargin']}</td>
            </tr></table>
            ";

            if ($mirrorMargins) {

                $headerE = "
                <table width=\"100%\" style=\"border-bottom: 1px solid #000000; vertical-align: bottom;  font-weight: bold; font-size: 14pt; color: #000088;\"><tr>
                <td width=\"33%\"><span style=\"\"><span style=\"\">{$params['headerRightMargin']}</span></span></td>
                <td width=\"33%\" align=\"center\"><img src=\"{$params['headerCentreMargin']}\" /></td>
                <td width=\"33%\" style=\"text-align: right;\"><span style=\"\">{$params['headerLeftMargin']}</span></td>
                </tr></table>
                ";

                $footerE = "
                <table width=\"100%\" style=\"vertical-align: bottom;  font-size: 14pt; color: #000088; font-weight: bold; font-style: italic;\"><tr>
                <td width=\"33%\"><span style=\"\">{$params['footerRightMargin']}</span></td>
                <td width=\"33%\" align=\"center\" style=\"\">{$params['footerCentreMargin']}</td>
                <td width=\"33%\" style=\"text-align: right; \">{$params['footerLeftMargin']}</td>
                </tr></table>
                ";
                if ($isHeaders) {
                    $mpdf->mirrorMargins = TRUE; // Use different Odd/Even headers and footers and mirror margins
                    $mpdf->SetHTMLHeader($headerE, 'E');
                }

                if ($isFooters) {
                    $mpdf->mirrorMargins = TRUE; // Use different Odd/Even headers and footers and mirror margins
                    $mpdf->SetHTMLFooter($footerE, 'E');
                }
            }

            if ($isHeaders) {
                $mpdf->SetHTMLHeader($header);
            }

            if ($isFooters) {
                $mpdf->SetHTMLFooter($footer);
            }



            if ($params['pathStylesheet']) {
                $stylesheet = file_get_contents($params['pathStylesheet']);
                $mpdf->WriteHTML($stylesheet, 1);
                $mpdf->WriteHTML($params['html'], 2);
            } else {
                $mpdf->WriteHTML($params['html']);
            }

            // Сохраним файл на серверном ресурсе пользователя 
            if ($params['pdfReport']) {
                $dirFilePDF = $params['pdfReport'];
                $mpdf->Output($dirFilePDF, 'F');
            } else {
                $mpdf->Output();
            }
        } catch (Exception $exc) {
            throw new Exception($exc->getMessage());
        }
    }

}

