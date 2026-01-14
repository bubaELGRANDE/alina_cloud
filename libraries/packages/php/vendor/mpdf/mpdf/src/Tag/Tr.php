<?php

namespace Mpdf\Tag;

use Mpdf\Css\Border;

class Tr extends Tag
{

	public function open($attr, &$ahtml, &$ihtml)
	{
		$level = $this->mpdf->tableLevel;
		$tbctr = $this->mpdf->tbctr[$level] ?? null;

		if ($tbctr === null) {
			$this->mpdf->tbctr[$level] = 0;
			$tbctr = 0;
		}

		if (!isset($this->mpdf->table[$level])) {
			$this->mpdf->table[$level] = [];
		}

		if (!isset($this->mpdf->table[$level][$tbctr])) {
			$this->mpdf->table[$level][$tbctr] = [];
		}

		if (!isset($this->mpdf->table[$level][$tbctr]['nr'])) {
			$this->mpdf->table[$level][$tbctr]['nr'] = 0;
		}

		$this->mpdf->table[$level][$tbctr]['nr']++;

		$this->mpdf->lastoptionaltag = 'TR'; // Guardar etiqueta opcional
		$this->cssManager->tbCSSlvl++;
		$this->mpdf->row++;
		$this->mpdf->col = -1;

		$properties = $this->cssManager->MergeCSS('TABLE', 'TR', $attr);

		if (
			!$this->mpdf->simpleTables &&
			(
				!isset($this->mpdf->table[$level][$tbctr]['borders_separate']) ||
				!$this->mpdf->table[$level][$tbctr]['borders_separate']
			)
		) {
			if (!empty($properties['BORDER-LEFT'])) {
				$this->mpdf->table[$level][$tbctr]['trborder-left'][$this->mpdf->row] = $properties['BORDER-LEFT'];
			}
			if (!empty($properties['BORDER-RIGHT'])) {
				$this->mpdf->table[$level][$tbctr]['trborder-right'][$this->mpdf->row] = $properties['BORDER-RIGHT'];
			}
			if (!empty($properties['BORDER-TOP'])) {
				$this->mpdf->table[$level][$tbctr]['trborder-top'][$this->mpdf->row] = $properties['BORDER-TOP'];
			}
			if (!empty($properties['BORDER-BOTTOM'])) {
				$this->mpdf->table[$level][$tbctr]['trborder-bottom'][$this->mpdf->row] = $properties['BORDER-BOTTOM'];
			}
		}

		if (isset($properties['BACKGROUND-COLOR'])) {
			if (!isset($this->mpdf->table[$level][$tbctr]['bgcolor']) || !is_array($this->mpdf->table[$level][$tbctr]['bgcolor'])) {
				$this->mpdf->table[$level][$tbctr]['bgcolor'] = [];
			}
			$this->mpdf->table[$level][$tbctr]['bgcolor'][$this->mpdf->row] = $properties['BACKGROUND-COLOR'];
		} elseif (isset($attr['BGCOLOR'])) {
			if (!isset($this->mpdf->table[$level][$tbctr]['bgcolor']) || !is_array($this->mpdf->table[$level][$tbctr]['bgcolor'])) {
				$this->mpdf->table[$level][$tbctr]['bgcolor'] = [];
			}
			$this->mpdf->table[$level][$tbctr]['bgcolor'][$this->mpdf->row] = $attr['BGCOLOR'];
		}

		/* -- BACKGROUNDS -- */
		if (isset($properties['BACKGROUND-GRADIENT']) && !$this->mpdf->kwt && !$this->mpdf->ColActive) {
			$this->mpdf->table[$level][$tbctr]['trgradients'][$this->mpdf->row] = $properties['BACKGROUND-GRADIENT'];
		}

		// FIXME: undefined variable $currblk (queda igual que en original)
		if (!empty($properties['BACKGROUND-IMAGE']) && !$this->mpdf->kwt && !$this->mpdf->ColActive) {
			$ret = $this->mpdf->SetBackground($properties, $currblk['inner_width']);
			if ($ret) {
				$this->mpdf->table[$level][$tbctr]['trbackground-images'][$this->mpdf->row] = $ret;
			}
		}
		/* -- END BACKGROUNDS -- */

		if (isset($properties['TEXT-ROTATE'])) {
			$this->mpdf->trow_text_rotate = $properties['TEXT-ROTATE'];
		}
		if (isset($attr['TEXT-ROTATE'])) {
			$this->mpdf->trow_text_rotate = $attr['TEXT-ROTATE'];
		}

		if ($this->mpdf->tablethead) {
			$this->mpdf->table[$level][$tbctr]['is_thead'][$this->mpdf->row] = true;
		}
		if ($this->mpdf->tabletfoot) {
			$this->mpdf->table[$level][$tbctr]['is_tfoot'][$this->mpdf->row] = true;
		}
	}

	public function close(&$ahtml, &$ihtml)
	{
		if ($this->mpdf->tableLevel) {
			// Si hay borde en TR - Actualiza borde derecho
			if (isset($this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-left'][$this->mpdf->row])) {
				$c = &$this->mpdf->cell[$this->mpdf->row][$this->mpdf->col];
				if ($c) {
					if ($this->mpdf->packTableData) {
						$cell = $this->mpdf->_unpackCellBorder($c['borderbin']);
					} else {
						$cell = $c;
					}
					$cell['border_details']['R'] = $this->mpdf->border_details(
						$this->mpdf->table[$this->mpdf->tableLevel][$this->mpdf->tbctr[$this->mpdf->tableLevel]]['trborder-right'][$this->mpdf->row]
					);
					$this->mpdf->setBorder($cell['border'], Border::RIGHT, $cell['border_details']['R']['s']);
					if ($this->mpdf->packTableData) {
						$c['borderbin'] = $this->mpdf->_packCellBorder($cell);
						unset($c['border'], $c['border_details']);
					} else {
						$c = $cell;
					}
				}
			}
			$this->mpdf->lastoptionaltag = '';
			unset($this->cssManager->tablecascadeCSS[$this->cssManager->tbCSSlvl]);
			$this->cssManager->tbCSSlvl--;
			$this->mpdf->trow_text_rotate = '';
			$this->mpdf->tabletheadjustfinished = false;
		}
	}
}
