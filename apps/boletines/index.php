<?php

/**
 * SimpleXLSX - Excel reader from PHP
 *
 * Copyright (c) 2010 - 2021 Sergey Shuchkin shuchkin.sergey@gmail.com
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   SimpleXLSX
 * @package    SimpleXLSX
 * @author     Sergey Shuchkin <shuchkin.sergey@gmail.com>
 * @copyright  2010 - 2021 Sergey Shuchkin
 * @license    http://www.opensource.org/licenses/mit-license.php MIT License
 * @version    0.9.1
 * @link       http://www.shuchkin.ru/simplexlsx/
 */

class SimpleXLSX {
	// Don't remove this string! It's used by script.
	const VERSION = '0.9.1';

	public static $CF = [ // Cell formats
		0  => 'General',
		1  => '0',
		2  => '0.00',
		3  => '#,##0',
		4  => '#,##0.00',
		9  => '0%',
		10 => '0.00%',
		11 => '0.00E+00',
		12 => '# ?/?',
		13 => '# ??/??',
		14 => 'mm-dd-yy',
		15 => 'd-mmm-yy',
		16 => 'd-mmm',
		17 => 'mmm-yy',
		18 => 'h:mm AM/PM',
		19 => 'h:mm:ss AM/PM',
		20 => 'h:mm',
		21 => 'h:mm:ss',
		22 => 'm/d/yy h:mm',
		37 => '#,##0 ;(#,##0)',
		38 => '#,##0 ;[Red](#,##0)',
		39 => '#,##0.00;(#,##0.00)',
		40 => '#,##0.00;[Red](#,##0.00)',
		45 => 'mm:ss',
		46 => '[h]:mm:ss',
		47 => 'mmss.0',
		48 => '##0.0E+0',
		49 => '@',
		// CHT
		27 => '[$-404]e/m/d',
		30 => 'm/d/yy',
		36 => '[$-404]e/m/d',
		50 => '[$-404]e/m/d',
		57 => '[$-404]e/m/d',
		// THA
		59 => 't0',
		60 => 't0.00',
		61 => 't#,##0',
		62 => 't#,##0.00',
		67 => 't0%',
		68 => 't0.00%',
		69 => 't# ?/?',
		70 => 't# ??/??',
	];
	public $cellFormats = [];
	public $datetimeFormat = 'Y-m-d H:i:s';
	public $debug;

	/* @var SimpleXMLElement $workbook */
	private $workbook;
	/* @var SimpleXMLElement[] $sheets */
	private $sheets;

	private $sheetNames = [];
	private $sheetFiles = [];
	// scheme
	private $styles;
	private $hyperlinks;
	/* @var array[] $package */
	private $package;
	private $sharedstrings;
	private $date1904 = 0;


	/*
		private $errno = 0;
		private $error = false;
	*/
	// XML schemas
	const SCHEMA_OFFICEDOCUMENT  = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument';
	const SCHEMA_RELATIONSHIP  = 'http://schemas.openxmlformats.org/package/2006/relationships';
	const SCHEMA_SHAREDSTRINGS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings';
	const SCHEMA_WORKSHEET     = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet';
	const SCHEMA_STYLES        = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles';

	public function __construct( $filename = null, $is_data = null, $debug = null ) {
		if ( $debug ) {
			$this->debug = $debug;
		}
		$this->package = [
			'filename' => '',
			'mtime'    => 0,
			'size'     => 0,
			'comment'  => '',
			'entries'  => []
		];
		if ( $filename && $this->_unzip( $filename, $is_data ) ) {
			$this->_parse();
		}
	}

	public static function parse( $filename = null, $is_data = null, $debug = null) {
		$xlsx = new self();
		$xlsx->debug = $debug;
		if ($xlsx->_unzip( $filename, $is_data )) {
			$xlsx->_parse();
		}
		if ( $xlsx->success()) {
			return $xlsx;
		}
		self::parseError($xlsx->error());
		self::parseErrno($xlsx->errno());

		return false;
	}
	public static function parseData( $data, $debug = false ) {
		return self::parse( $data, true, $debug );
	}
	public static function getInfo($filename, $is_data = false, $debug = false){
		$xlsx = new self();
		$xlsx->debug = $debug;
		if ($xlsx->_unzip( $filename, $is_data, true )) {
			return $xlsx->package;
		}
		self::parseError($xlsx->error());
		self::parseErrno($xlsx->errno());
		return false;
	}

	public function success() {
		return ! (self::$errno || self::$error);
	}
	private static $errno = 0;
	private static $error = '';
	public function errno(){
		return self::$errno;
	}
	public function error(){
		return self::$error;
	}
	private static function parseError( $set = false ) {
		if ($set) {
			self::$error = $set;
		}
		return self::$error;
	}
	private static function parseErrno( $set = false ) {
		if ($set) {
			self::$errno = $set;
		}
		return self::$errno;
	}
	private function _parse() {
		// Document relations
		$rels = simplexml_load_string( $this->_getFromIndex( '_rels/.rels' ) );
		if (!$rels) {
			return false;
		}
		foreach ( $rels->Relationship as $rel ) {
			if ( $rel['Type'] == self::SCHEMA_OFFICEDOCUMENT ) {
				// workbook
				$this->workbook = simplexml_load_string( $this->_getFromIndex( $rel['Target'] ) );
				if ( ! $this->workbook ) {
					return false;
				}

				$this->date1904 = (int)( (string) $this->workbook->workbookPr['date1904']);
//				if ( $this->debug ) {
//					var_dump( $this->date1904 );
//				}
				$x = dirname( $rel['Target'] );
				$workbook_rels_path = ($x === '.') ? '_rels/workbook.xml.rels' : $x . '/_rels/workbook.xml.rels';
				$workbook_rels_path = str_replace( '\\', '/', $workbook_rels_path );

				$workbook_rels = simplexml_load_string( $this->_getFromIndex( $workbook_rels_path ) );

				if ( $this->workbook->sheets && $this->workbook->sheets->sheet ) {
					foreach ( $this->workbook->sheets->sheet as $s ) {
						$this->sheetNames[ (int) $s['sheetId'] ] = (string) $s['name'];
					}
				}

				if ( $workbook_rels ) {
					foreach ( $workbook_rels->Relationship as $wrel ) {
						$wrel_type = (string) $wrel['Type'];
						$wrel_target = (string) $wrel['Target'];
						switch ( $wrel_type ) {
							case self::SCHEMA_WORKSHEET:
								$this->sheetFiles[ (int) $wrel['id'] ] = $wrel_target;
								break;
							case self::SCHEMA_SHAREDSTRINGS:
								$this->sharedstrings = simplexml_load_string( $this->_getFromIndex( $this->_getTarget( $rel['Target'], $wrel_target ) ) );
								break;
							case self::SCHEMA_STYLES:
								$this->styles = simplexml_load_string( $this->_getFromIndex( $this->_getTarget( $rel['Target'], $wrel_target ) ) );
								if ( $this->styles && $this->styles->cellXfs && $this->styles->cellXfs->xf ) {
									foreach ( $this->styles->cellXfs->xf as $xf ) {
										$this->cellFormats[] = (string) $xf['numFmtId'];
									}
								}
								break;
						}
					}
				}
				break;
			}
		}
		if (count($this->sheetFiles)) {
			// Sort sheets
			$s = [];
			foreach ($this->sheetNames as $i => $n) {
				foreach ($this->workbook->sheets->sheet as $sheet) {
					if ($sheet['name'] == $n) {
						$rId = (string) $sheet->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships')['id'];
						$s[$i] = $this->sheetFiles[$rId];
					}
				}
			}
			$this->sheetFiles = $s;
		}

		return true;
	}
	/*
	 * @param string $path
	 *
	 * @return SimpleXMLElement
	 * */
	public function getSheet( $path ) {
		if ( isset( $this->sheets[ $path ] ) ) {
			return $this->sheets[ $path ];
		}
		$this->sheets[ $path ] = simplexml_load_string( $this->_getFromIndex( $path ) );

		return $this->sheets[ $path ];
	}
	public function getSheetNames( $xlsx = null ) {
		return $this->sheetNames;
	}

	public function getSheetCount(){
		return count($this->sheetNames);
	}

	private function _getTarget( $base, $target ) {
		$target = str_replace('\\', '/', $target);
		$base = str_replace('\\', '/', $base);
		$path = dirname( $base ) . '/' . $target;
		$path = preg_replace( '#/./#', '/', $path );
		$path = preg_replace( '#/([^/.]+?)/../#', '/', $path );

		return $path;
	}

	// gets worksheet relationships
	public function getSheetRels( $sheet_path ) {
		$path = dirname( $sheet_path ) . '/_rels/' . basename( $sheet_path ) . '.rels';
		$rels = simplexml_load_string( $this->_getFromIndex( $path ) );
		if ( ! $rels ) {
			return false;
		}

		$this->hyperlinks = [];
		// hyperlink
		foreach ( $rels->Relationship as $rel ) {
			if ( $rel['Type'] === 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/hyperlink' ) {
				$this->hyperlinks[ (string) $rel['Id'] ] = (string) $rel['Target'];
			}
		}
		return $rels;
	}
	public function getStyles() {
		return $this->styles;
	}
	public function getPackage() {
		return $this->package;
	}
	public function getSharedStrings() {
		return $this->sharedstrings;
	}

	public function getSheetData( $sheetNameOrId ) {

		if ( is_numeric( $sheetNameOrId )) {
            if (!isset($this->sheetFiles[ $sheetNameOrId ])) {
                // Si el ID de hoja no existe, se intenta usar la primera disponible.
                $sheet_path = reset($this->sheetFiles);
                if ($sheet_path === false) {
                    return false; // No se encontraron hojas
                }
            } else {
                $sheet_path = $this->sheetFiles[ $sheetNameOrId ];
            }
			$sheet = $this->getSheet( $sheet_path );
			if ($sheet === false) {
				return false;
			}
			$this->getSheetRels( $sheet_path );
		} else {
			$id = array_search( $sheetNameOrId, $this->sheetNames, true );
			if ($id === false) {
				return false;
			}
			$sheet = $this->getSheet( $this->sheetFiles[ $id ] );
			if ($sheet === false) {
				return false;
			}
			$this->getSheetRels( $this->sheetFiles[ $id ] );
		}
		if ( isset( $sheet->sheetData, $sheet->sheetData->row ) ) {
			return $sheet->sheetData->row;
		}

		return false;
	}

	public function rows( $sheetNameOrId = 0 ) {
		$rows = $this->getSheetData( $sheetNameOrId );
		if ( ! $rows ) {
			return false;
		}
		$i = 0;
		$res = [];
		foreach ( $rows as $row ) {
			$res[ $i ] = $this->row($row, $i);
			$i++;
		}
		return $res;
	}
	public function row( $row, $rowIndex = -1) {
		$r = [];
		$curC = 0;
		foreach ( $row->c as $c ) {
			list( $cell, $curC ) = $this->cell( $c, $curC, $rowIndex );
			$r[ $curC ] = $cell;
			$curC++;
		}
		return $r;
	}

	public function cell( $c, $curC = 0, $rowIndex = -1 ) {

		// cell address, column horizontal
		$a = (string) $c['r'];
		$col = preg_replace( '/[0-9]/', '', $a );
		$row = preg_replace( '/[A-Z]/', '', $a );

		if ( $this->debug ) {
			// C: 16, A: 65
			$c_index = ord($col[0]) - 65;
			if ( strlen($col) > 1) {
				$c_index = ( ( $c_index + 1 ) * 26 ) + ord($col[1]) - 65;
			}
			if ( $curC < $c_index ) {
				$curC = $c_index;
			}
		}

		$s = (int) $c['s'];
		if ( isset( $c->f ) ) {
			$r = (string) $c->f;
		} else if ( isset( $c->v ) ) {
			$r = (string) $c->v;
		} else {
			$r = '';
		}

		// t value in c element
		// s = shared, b = boolean, e = error, n = number, d = date, str = string
		$t = (string) $c['t'];
		$s = (int) $c['s'];

		if ( $t === 's' ) { // shared string
			if ( isset($this->sharedstrings->si[ (int) $r ]->t) ) {
				$r = (string) $this->sharedstrings->si[ (int) $r ]->t;
			} else if ( isset($this->sharedstrings->si[ (int) $r ]->r) ) {
				$r = $this->_parseRichText($this->sharedstrings->si[ (int) $r ]);
			}
		} else if ( $t === 'b' ) { // boolean
			$r = ($r ) ? ( $r === '1' || $r === 'true') : false;
		} else if ( $s > 0 && isset( $this->cellFormats[ $s ] ) ) { // styles
			$format = $this->cellFormats[ $s ];
			if ( preg_match( '/[mM]/', $format ) ) { // [m]
				$r = $this->timestamp( $r );
			}
		}

		if ( isset($c->hyperlink) ) {
			$h = $c->hyperlink->attributes('http://schemas.openxmlformats.org/officeDocument/2006/relationships');
			$r = $this->hyperlinks[ (string) $h['id'] ];
		}
		return [ $r, $curC ];
	}
	public function rowsEx( $sheetNameOrId = 0 ) {
		$rows = $this->getSheetData( $sheetNameOrId );
		if ( ! $rows ) {
			return false;
		}
		$i = 0;
		$res = [];
		foreach ( $rows as $row ) {
			$j = 0;
			$res_row = [];
			foreach ( $row->c as $c ) {
				list( $cell, $j ) = $this->cell( $c, $j, $i );
				$res_row[ $this->num2alpha( $j ) ] = $cell;
				$j ++;
			}
			$res[ $i ] = $res_row;
			$i ++;
		}
		return $res;
	}

	public function toHTML( $sheetNameOrId = 0, $classes = 'simplexlsx' ) {
		$rows = $this->rows( $sheetNameOrId );
		if (!$rows) {
			return false;
		}
		$s = '<table class="'.$classes.'">';
		foreach( $rows as $r ) {
			$s .= '<tr>';
			foreach( $r as $c ) {
				$s .= '<td>'.($c === null ? '' : htmlentities($c, ENT_QUOTES | ENT_HTML5 )).'</td>';
			}
			$s .= '</tr>';
		}
		$s .= '</table>';
		return $s;
	}
	public static function num2alpha( $n ) {
		for ( $r = ''; $n >= 0; $n = (int) ( $n / 26 ) - 1 ) {
			$r = chr( $n % 26 + 0x41 ) . $r;
		}
		return $r;
	}
	public function sheets() {
		return $this->sheetNames;
	}

	public function sheetsCount() {
		return count( $this->sheetNames );
	}

	public function sheetName( $id ) {
		return isset( $this->sheetNames[ $id ] ) ? $this->sheetNames[ $id ] : false;
	}
	public function sheetNames(){
		return $this->sheetNames;
	}
	// XLSX to CSV
	public function toCSV( $sheetNameOrId = 0, $delimiter = ',', $enclosure = '"' ) {

		$rows = $this->rows( $sheetNameOrId );
		if (!$rows) {
			return false;
		}
		$f = fopen( 'php://temp', 'rb+' );
		foreach( $rows as $row ) {
			fputcsv( $f, $row, $delimiter, $enclosure );
		}
		rewind( $f );
		$csv = stream_get_contents( $f );
		fclose( $f );

		return $csv;
	}

	public function timestamp( $val ) {
		$d = floor( $val );
		$t = $val - $d;
		if ( $this->date1904 ) {
			$d += 1462;
		}
		// on Windows, the max timestamp is 2038-01-19 03:14:07
		$t = (int) ( $d > 0 ) ? ( $d - 25569 ) * 86400 + round( $t * 86400 ) : round( $t * 86400 );
		return $t;
	}
	public function unixstamp( $val ) {
		return $this->timestamp($val);
	}
	public function toDate( $val ) {
		return gmdate( $this->datetimeFormat, $this->timestamp( $val ) );
	}
	public function toDateTime( $val ) {
		return $this->toDate( $val );
	}

	public function getCell( $sheetNameOrId = 0, $address = 'A1' ) {
		$sheet = $this->getSheetData( $sheetNameOrId );
		if (!$sheet) {
			return false;
		}
		$row_num = (int) preg_replace( '/[A-Z]/', '', $address ) - 1;
		$col_char = preg_replace( '/[0-9]/', '', $address );
		$col_num = 0;
		for($i = 0, $l = strlen($col_char); $i < $l; $i++) {
			$col_num = $col_num * 26 + ord($col_char[$i]) - 64;
		}
		$col_num--;

		if ( isset( $sheet->row[ $row_num ], $sheet->row[ $row_num ]->c[ $col_num ] ) ) {
			$c = $sheet->row[ $row_num ]->c[ $col_num ];
			list( $cell, ) = $this->cell( $c );
			return $cell;
		}
		return null;
	}
	public function _parseRichText( $is ) {
		$value = [];
		if ( isset( $is->t ) ) {
			$value[] = (string) $is->t;
		} else if ( isset( $is->r ) ) {
			foreach ( $is->r as $run ) {
				$value[] = (string) $run->t;
			}
		}
		return implode( '', $value );
	}
	private function _unzip( $filename, $is_data = false, $info_only = false ) {
		// Clear current file
		$this->package = [
			'filename' => '',
			'mtime'    => 0,
			'size'     => 0,
			'comment'  => '',
			'entries'  => []
		];
		if ( $is_data ) {
			$this->package['filename'] = 'data.zip';
			$this->package['mtime']    = time();
			$this->package['size']     = strlen( $filename );
			$vZ = $filename;
		} else {
			if ( ! is_readable( $filename ) ) {
				self::parseErrno(1);
				self::parseError('File not found ' . $filename );
				return false;
			}
			// Get file info
			$this->package['filename'] = $filename;
			$this->package['mtime']    = filemtime( $filename );
			$this->package['size']     = filesize( $filename );
			// Read file
			$vZ = file_get_contents( $filename );
		}
		// Cut end of central directory
		$aE = explode( "\x50\x4b\x05\x06", $vZ );
		if ( count( $aE ) === 1 ) {
			self::parseErrno(2);
			self::parseError('Unknown format');
			return false;
		}
		if ($this->debug) {
			print_r($aE);
			echo 'Central directory start: ' . strrpos($vZ, "\x50\x4b\x01\x02");
		}
		// Explode to each part
		$aE = explode( "\x50\x4b\x01\x02", $vZ );
		// Shift out spanning signature or empty entry
		array_shift( $aE );
		// Loop through the entries
		foreach ( $aE as $fZ ) {
			$d = [];
			$d['crc_ ৩০']     = unpack( 'V', substr( $fZ, 16, 4 ) )[1];
			$d['compressed_size']   = unpack( 'V', substr( $fZ, 20, 4 ) )[1];
			$d['size']              = unpack( 'V', substr( $fZ, 24, 4 ) )[1];
			$lp                     = unpack( 'v', substr( $fZ, 28, 2 ) )[1];
			$d['name']              = substr( $fZ, 46, $lp );
			$d['mtime']             = mktime( ( unpack( 'v', substr( $fZ, 14, 2 ) )[1] & 0xFE00 ) >> 9, ( unpack( 'v', substr( $fZ, 14, 2 ) )[1] & 0x01E0 ) >> 5, ( unpack( 'v', substr( $fZ, 14, 2 ) )[1] & 0x001F ) << 1, ( unpack( 'v', substr( $fZ, 12, 2 ) )[1] & 0x01E0 ) >> 5, ( unpack( 'v', substr( $fZ, 12, 2 ) )[1] & 0x001F ), ( ( unpack( 'v', substr( $fZ, 12, 2 ) )[1] & 0xFE00 ) >> 9 ) + 1980 );
			$d['stored_method']     = unpack( 'v', substr( $fZ, 10, 2 ) )[1];
			$this->package['entries'][] = $d;
		}
		$this->package['comment'] = substr( $aE[ count( $aE ) - 1 ], 46 + $lp + unpack( 'v', substr( $aE[ count( $aE ) - 1 ], 30, 2 ) )[1] );

		if ($info_only) {
			return true;
		}

		// get data from local file header
		$aE = explode( "\x50\x4b\x03\x04", $vZ );
		array_shift( $aE );
		foreach ( $aE as $i => $fZ ) {
			if ($i >= count($this->package['entries'])) {
				break;
			}
			$d = &$this->package['entries'][$i];

			$d['data_offset'] = strpos( $fZ, "\x50\x4b\x07\x08" ) + 30 + unpack( 'v', substr( $fZ, 26, 2 ) )[1] + unpack( 'v', substr( $fZ, 28, 2 ) )[1];
			$d['data_offset'] = strpos( $fZ, substr( $fZ, 26, 2) ) + 30;

			$lp = unpack( 'v', substr( $fZ, 26, 2 ) )[1];
			$d['name'] = substr( $fZ, 30, $lp );

			$d['data_offset'] = strpos($fZ, $d['name']) + $lp;
			$d['data_offset'] = 30 + unpack( 'v', substr( $fZ, 26, 2 ) )[1] + unpack( 'v', substr( $fZ, 28, 2 ) )[1];

			if ( $d['stored_method'] === 0 ) { // no compression
				$d['data'] = substr( $fZ, $d['data_offset'], $d['size'] );
			} else if ( $d['stored_method'] === 8 ) { // Deflate
				$d['data'] = gzinflate( substr( $fZ, $d['data_offset'], $d['compressed_size'] ) );
			} else {
				self::parseErrno(3);
				self::parseError('Unsupported compression method ' . $d['stored_method']);
				return false;
			}
			if ( $d['data'] === false ) {
				self::parseErrno(4);
				self::parseError('gzinflate error');
				return false;
			}
			if ( strlen( $d['data'] ) !== $d['size'] ) {
				self::parseErrno(5);
				self::parseError('Data size mismatch');
				return false;
			}
		}
		return true;
	}

	public function _getFromIndex( $name ) {
		foreach ( $this->package['entries'] as $e ) {
			if ( $e['name'] === $name ) {
				return $e['data'];
			}
		}
		self::parseErrno(6);
		self::parseError('File not found in archive ' . $name);
		return false;
	}
}


// INICIO DEL PROCESAMIENTO DEL FORMULARIO
$errorMessage = '';
$successMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['excelFile'])) {
    $file = $_FILES['excelFile'];

    // 1. Validar el archivo
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessage = 'Error al subir el archivo. Código: ' . $file['error'];
    } else {
        $fileType = mime_content_type($file['tmp_name']);
        $allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', // .xlsx
            'application/vnd.ms-excel' // .xls (aunque la librería funciona mejor con xlsx)
        ];

        if (!in_array($fileType, $allowedTypes)) {
            $errorMessage = 'Tipo de archivo no válido. Por favor, sube un archivo .xlsx';
        } else {
            // 2. Procesar el archivo si es válido
            if ($xlsx = SimpleXLSX::parse($file['tmp_name'])) {
                $processedData = [];
                // Itera sobre cada fila del archivo Excel
                foreach ($xlsx->rows() as $row) {
                    // Corta la fila para mantener solo las primeras 5 columnas (A, B, C, D, E)
                    $processedData[] = array_slice($row, 0, 5);
                }

                // 3. Generar y enviar el archivo CSV para descarga
                header('Content-Type: text/csv; charset=utf-8');
                header('Content-Disposition: attachment; filename="archivo_modificado.csv"');

                // Abre el flujo de salida de PHP para escribir el CSV
                $output = fopen('php://output', 'w');
                
                // Escribe los datos procesados en el archivo CSV
                foreach ($processedData as $rowData) {
                    fputcsv($output, $rowData);
                }
                
                fclose($output);
                exit(); // Detiene la ejecución para que no se renderice el HTML después de la descarga
            } else {
                $errorMessage = 'Error al procesar el archivo Excel: ' . SimpleXLSX::parseError();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesador de Archivos Excel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .file-input-label {
            transition: all 0.2s ease-in-out;
        }
        .file-input-label:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        #spinner {
            display: none;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="w-full max-w-lg mx-auto bg-white rounded-2xl shadow-xl p-8 md:p-10">
        
        <header class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Procesador de Excel</h1>
            <p class="text-gray-500 mt-2">Sube tu archivo .xlsx para eliminar las columnas desde la F en adelante.</p>
        </header>

        <?php if ($errorMessage): ?>
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert">
                <p class="font-bold">Error</p>
                <p><?php echo htmlspecialchars($errorMessage); ?></p>
            </div>
        <?php endif; ?>
        
        <form id="upload-form" action="" method="POST" enctype="multipart/form-data">
            <div class="mb-6">
                <label for="excelFile" class="file-input-label cursor-pointer w-full flex flex-col items-center px-4 py-10 bg-white text-blue-500 rounded-lg shadow-lg border-2 border-dashed border-gray-300 hover:bg-blue-50 hover:border-blue-500">
                    <svg class="w-12 h-12" fill="currentColor" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path d="M16.88 9.1A4 4 0 0 1 16 17H5a5 5 0 0 1-1-9.9V7a3 3 0 0 1 4.52-2.59A4.98 4.98 0 0 1 17 8c0 .38-.04.74-.12 1.1zM11 11h3l-4 4-4-4h3V9h2v2z" />
                    </svg>
                    <span id="file-name" class="mt-2 text-base leading-normal text-gray-600">Selecciona o arrastra un archivo</span>
                    <input type="file" class="hidden" id="excelFile" name="excelFile" accept=".xlsx,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                </label>
            </div>
            
            <div class="flex justify-center">
                <button type="submit" id="submit-btn" class="w-full flex items-center justify-center bg-blue-600 text-white font-bold py-3 px-6 rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-4 focus:ring-blue-300 transition duration-300 ease-in-out disabled:bg-gray-400">
                    <svg id="spinner" class="w-5 h-5 mr-3 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    <span id="button-text">Procesar y Descargar</span>
                </button>
            </div>
        </form>

        <div class="mt-8 text-center text-gray-500 text-sm">
            <p><strong>¿Cómo funciona?</strong></p>
            <ol class="list-decimal list-inside text-left mt-2 space-y-1">
                <li>Sube tu archivo Excel (.xlsx).</li>
                <li>El script conserva las columnas de la A a la E.</li>
                <li>Se genera un archivo <strong class="text-gray-700">.csv</strong> con los datos limpios.</li>
                <li>Tu navegador iniciará la descarga automáticamente.</li>
            </ol>
        </div>

    </div>

    <script>
        const form = document.getElementById('upload-form');
        const fileInput = document.getElementById('excelFile');
        const fileNameDisplay = document.getElementById('file-name');
        const submitBtn = document.getElementById('submit-btn');
        const spinner = document.getElementById('spinner');
        const buttonText = document.getElementById('button-text');

        // Muestra el nombre del archivo seleccionado
        fileInput.addEventListener('change', () => {
            if (fileInput.files.length > 0) {
                fileNameDisplay.textContent = fileInput.files[0].name;
            } else {
                fileNameDisplay.textContent = 'Selecciona o arrastra un archivo';
            }
        });

        // Muestra un estado de "cargando" al enviar el formulario
        form.addEventListener('submit', () => {
            if (fileInput.files.length > 0) {
                submitBtn.disabled = true;
                spinner.style.display = 'block';
                buttonText.textContent = 'Procesando...';
            }
        });

        // Opcional: Para resetear el botón si el usuario vuelve a la página sin recargar
        window.addEventListener('pageshow', (event) => {
            if (event.persisted) {
                form.reset();
                fileNameDisplay.textContent = 'Selecciona o arrastra un archivo';
                submitBtn.disabled = false;
                spinner.style.display = 'none';
                buttonText.textContent = 'Procesar y Descargar';
            }
        });
    </script>
</body>
</html>

