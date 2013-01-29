<?php

define( 'ANTHOLOGIZE_IMAGES_PATH', ANTHOLOGIZE_INSTALL_PATH . 'images/' );

//need to override addTOC() to adjust for printed page numbering and the TOC page numbering.
class AnthologizeTCPDF extends TCPDF {

	/**
	 * Output a Table of Content Index (TOC).
	 *
	 * Overriding to dig up the print page numbers, instead of PDF page numbers, for TOC
	 *
	 * Before calling this method you have to open the page using the addTOCPage() method.
	 * After calling this method you have to call endTOCPage() to close the TOC page.
	 * You can override this method to achieve different styles.
	 * @param int $page page number where this TOC should be inserted (leave empty for current page).
	 * @param string $numbersfont set the font for page numbers (please use monospaced font for better alignment).
	 * @param string $filler string used to fill the space between text and page number.
	 * @param string $toc_name name to use for TOC bookmark.
	 * @access public
	 * @author Nicola Asuni
	 * @since 4.5.000 (2009-01-02)
	 * @see addTOCPage(), endTOCPage(), addHTMLTOC()
	 */


	public function addTOC($page='', $numbersfont='', $filler='.', $toc_name='TOC', $style='', $color=array(0,0,0)) {
		$fontsize = $this->FontSizePt;
		$fontfamily = $this->FontFamily;
		$fontstyle = $this->FontStyle;
		$w = $this->w - $this->lMargin - $this->rMargin;
		$spacer = $this->GetStringWidth(chr(32)) * 4;
		$page_first = $this->getPage();
		$lmargin = $this->lMargin;
		$rmargin = $this->rMargin;
		$x_start = $this->GetX();
		$current_page = $this->page;
		$current_column = $this->current_column;
		if ($this->empty_string($numbersfont)) {
			$numbersfont = $this->default_monospaced_font;
		}
		if ($this->empty_string($filler)) {
			$filler = ' ';
		}
		if ($this->empty_string($page)) {
			$gap = ' ';
		} else {
			$gap = '';
			if ($page < 1) {
				$page = 1;
			}
		}

		foreach ($this->outlines as $key => $outline) {
			if ($this->rtl) {
				$aligntext = 'R';
				$alignnum = 'L';
			} else {
				$aligntext = 'L';
				$alignnum = 'R';
			}
			if ($outline['l'] == 0) {
				$this->SetFont($fontfamily, $fontstyle.'B', $fontsize);
			} else {
				$this->SetFont($fontfamily, $fontstyle, $fontsize - $outline['l']);
			}
			// check for page break
			$this->checkPageBreak(($this->FontSize * $this->cell_height_ratio));
			// set margins and X position
			if (($this->page == $current_page) AND ($this->current_column == $current_column)) {
				$this->lMargin = $lmargin;
				$this->rMargin = $rmargin;
			} else {
				if ($this->current_column != $current_column) {
					if ($this->rtl) {
						$x_start = $this->w - $this->columns[$this->current_column]['x'];
					} else {
						$x_start = $this->columns[$this->current_column]['x'];
					}
				}
				$lmargin = $this->lMargin;
				$rmargin = $this->rMargin;
				$current_page = $this->page;
				$current_column = $this->current_column;
			}
			$this->SetX($x_start);
			$indent = ($spacer * $outline['l']);
			if ($this->rtl) {
				$this->rMargin += $indent;
				$this->x -= $indent;
			} else {
				$this->lMargin += $indent;
				$this->x += $indent;
			}
			$link = $this->AddLink();
			$this->SetLink($link, 0, $outline['p']);
			// write the text

			$this->Write(0, $outline['t'], $link, 0, $aligntext, false, 0, false, false, 0);
			$this->SetFont($numbersfont, $fontstyle, $fontsize);
			if ($this->empty_string($page)) {
				$pagenum = $outline['p'];
			} else {
				// placemark to be replaced with the correct number
				$pagenum = '{#'.($outline['p']).'}';
				if ($this->isUnicodeFont()) {
					$pagenum = '{'.$pagenum.'}';
				}
			}
			$numwidth = $this->GetStringWidth($pagenum);
			if ($this->rtl) {
				$tw = $this->x - $this->lMargin;
			} else {
				$tw = $this->w - $this->rMargin - $this->x;
			}
			$fw = $tw - $numwidth - $this->GetStringWidth(chr(32));
			$numfills = floor($fw / $this->GetStringWidth($filler));
			if ($numfills > 0) {
				$rowfill = str_repeat($filler, $numfills);
			} else {
				$rowfill = '';
			}
			if ($this->rtl) {
				$pagenum = $pagenum.$gap.$rowfill.' ';
			} else {
				$pagenum = ' '.$rowfill.$gap.$pagenum;
			}
			// write the number
			$this->Cell($tw, 0, $pagenum, 0, 1, $alignnum, 0, $link, 0);
		}
		$page_last = $this->getPage();
		$numpages = $page_last - $page_first + 1;
		if (!$this->empty_string($page)) {
			for ($p = $page_first; $p <= $page_last; ++$p) {
				// get page data
				$temppage = $this->getPageBuffer($p);



				for ($n = 1; $n <= $this->numpages; ++$n) {
					// update page numbers
					$k = '{#'.$n.'}';
					$ku = '{'.$k.'}';
					$alias_a = $this->_escape($k);
					$alias_au = $this->_escape($ku);
					if ($this->isunicode) {
						$alias_b = $this->_escape($this->UTF8ToLatin1($k));
						$alias_bu = $this->_escape($this->UTF8ToLatin1($ku));
						$alias_c = $this->_escape($this->utf8StrRev($k, false, $this->tmprtl));
						$alias_cu = $this->_escape($this->utf8StrRev($ku, false, $this->tmprtl));
					}
					if ($n >= $page) {
						$np = $n + $numpages;
					} else {
						$np = $n;
					}

//only change to original method is here
//since $page is the page where the TOC is being inserted, subtracting it again from $np gets the printed page
//numbers to match up with the numbering in the TOC

					$ns = $this->formatTOCPageNumber($np - $page );


					$nu = $ns;
					$sdiff = strlen($k) - strlen($ns) - 1;
					$sdiffu = strlen($ku) - strlen($ns) - 1;
					$sfill = str_repeat($filler, $sdiff);
					$sfillu = str_repeat($filler, $sdiffu);
					if ($this->rtl) {
						$ns = $ns.' '.$sfill;
						$nu = $nu.' '.$sfillu;
					} else {
						$ns = $sfill.' '.$ns;
						$nu = $sfillu.' '.$nu;
					}
					$nu = $this->UTF8ToUTF16BE($nu, false);
					$temppage = str_replace($alias_au, $nu, $temppage);
					if ($this->isunicode) {
						$temppage = str_replace($alias_bu, $nu, $temppage);
						$temppage = str_replace($alias_cu, $nu, $temppage);
						$temppage = str_replace($alias_b, $ns, $temppage);
						$temppage = str_replace($alias_c, $ns, $temppage);
					}
					$temppage = str_replace($alias_a, $ns, $temppage);
				}
				// save changes
				$this->setPageBuffer($p, $temppage);
			}
			// move pages
			$this->Bookmark($toc_name, 0, 0, $page_first);
			for ($i = 0; $i < $numpages; ++$i) {
				$this->movePage($page_last, $page);
			}
		}
	}

	/**
	 * This method is used to render the page header.
	 *
	 * Overrides TCPDF::Header(), so that we can specify our own file path for the header image
	 * and section and part titles
	 *
	 * @package Anthologize
	 * @since 0.6
	 */
	public function Header() {
		if ($this->header_xobjid < 0) {
			// start a new XObject Template
			$this->header_xobjid = $this->startTemplate($this->w, $this->tMargin);
			$headerfont = $this->getHeaderFont();
			$headerdata = $this->getHeaderData();

			$this->y = $this->header_margin;
			if ($this->rtl) {
				$this->x = $this->w - $this->original_rMargin;
			} else {
				$this->x = $this->original_lMargin;
			}

			if (($headerdata['logo']) AND ($headerdata['logo'] != K_BLANK_IMAGE)) {

				$imgtype = $this->getImageFileType( ANTHOLOGIZE_IMAGES_PATH . $headerdata['logo']);
				if (($imgtype == 'eps') OR ($imgtype == 'ai')) {
					$this->ImageEps( ANTHOLOGIZE_IMAGES_PATH .$headerdata['logo'], '', '', $headerdata['logo_width']);
				} elseif ($imgtype == 'svg') {
					$this->ImageSVG( ANTHOLOGIZE_IMAGES_PATH .$headerdata['logo'], '', '', $headerdata['logo_width']);
				} else {
					$this->Image( ANTHOLOGIZE_IMAGES_PATH . $headerdata['logo'], '', '', $headerdata['logo_width']);
				}
				$imgy = $this->getImageRBY();

			} else {
				$imgy = $this->y;
			}

			$cell_height = round(($this->cell_height_ratio * $headerfont[2]) / $this->k, 2);
			// set starting margin for text data cell
			if ($this->getRTL()) {
				$header_x = $this->original_rMargin + ($headerdata['logo_width'] * 1.1);
			} else {
				$header_x = $this->original_lMargin + ($headerdata['logo_width'] * 1.1);
			}
			$cw = $this->w - $this->original_lMargin - $this->original_rMargin - ($headerdata['logo_width'] * 1.1);
			$this->SetTextColor(0, 0, 0);
			// header title
			$this->SetFont($headerfont[0], 'B', $headerfont[2] + 1);
			$this->SetX($header_x);
			$this->Cell($cw, $cell_height, $headerdata['title'], 0, 1, '', 0, '', 0);

			// header string
			$this->SetFont($headerfont[0], $headerfont[1], $headerfont[2]);
			$this->SetX($header_x);
			$this->MultiCell($cw, $cell_height, $headerdata['string'], 0, '', 0, 1, '', '', true, 0, false, true, 0, 'T', false);
			// print an ending header line
			$this->SetLineStyle(array('width' => 0.85 / $this->k, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
			$this->SetY((2.835 / $this->k) + max($imgy, $this->y));
			if ($this->rtl) {
				$this->SetX($this->original_rMargin);
			} else {
				$this->SetX($this->original_lMargin);
			}
			$this->Cell(($this->w - $this->original_lMargin - $this->original_rMargin), 0, '', 'T', 0, 'C');
			$this->endTemplate();
		}
		// print header template
		$x = 0;
		$dx = 0;
		if ($this->booklet AND (($this->page % 2) == 0)) {
			// adjust margins for booklet mode
			$dx = ($this->original_lMargin - $this->original_rMargin);
		}
		if ($this->rtl) {
			$x = $this->w + $dx;
		} else {
			$x = 0 + $dx;
		}
		$this->printTemplate($this->header_xobjid, $x, 0, 0, 0, '', '', false);
		if ($this->header_xobj_autoreset) {
			// reset header xobject template at each page
			$this->header_xobjid = -1;
		}
	}


	public function Footer() {
		$cur_y = $this->GetY();
		$ormargins = $this->getOriginalMargins();
		$this->SetTextColor(0, 0, 0);
		//set style for cell border
		$line_width = 0.85 / $this->getScaleFactor();
		$this->SetLineStyle(array('width' => $line_width, 'cap' => 'butt', 'join' => 'miter', 'dash' => 0, 'color' => array(0, 0, 0)));
		//print document barcode
		$barcode = $this->getBarcode();
		if (!empty($barcode)) {
			$this->Ln($line_width);
			$barcode_width = round(($this->getPageWidth() - $ormargins['left'] - $ormargins['right']) / 3);
			$style = array(
				'position' => $this->rtl?'R':'L',
				'align' => $this->rtl?'R':'L',
				'stretch' => false,
				'fitwidth' => true,
				'cellfitalign' => '',
				'border' => false,
				'padding' => 0,
				'fgcolor' => array(0,0,0),
				'bgcolor' => false,
				'text' => false
			);
			$this->write1DBarcode($barcode, 'C128B', '', $cur_y + $line_width, '', (($this->getFooterMargin() / 3) - $line_width), 0.3, $style, '');
		}

		// If a language file isn't loaded for some reason, we'll use an empty string
		if ( empty( $this->l['w_page'] ) )
			$this->l['w_page'] = '';

		//Anthologize change: remove /totalpages
		if (empty($this->pagegroups)) {
			$pagenumtxt = $this->l['w_page'].' '.$this->getAliasNumPage();
		} else {
			$pagenumtxt = $this->l['w_page'].' '.$this->getPageNumGroupAlias();
		}
		$this->SetY($cur_y);

		//Print page number
		if ($this->getRTL()) {
			$this->SetX($ormargins['right']);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'L');
		} else {
			$this->SetX($ormargins['left']);
			$this->Cell(0, 0, $pagenumtxt, 'T', 0, 'R');
		}
	}

}

