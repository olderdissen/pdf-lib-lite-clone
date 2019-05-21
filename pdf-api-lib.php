<?
################################################################################
# copyright 2019 by Markus Olderdissen
# free for private use or inspiration.
# public use need written permission.
################################################################################

################################################################################
# _pdf_begin_page ( array $pdf , int $width , int $height ) : void
################################################################################

function _pdf_begin_page(& $pdf, $width, $height)
	{
	$pdf["width"] = $width;
	$pdf["height"] = $height;
	$pdf["stream"] = array
		(
		);
	}

################################################################################
# _pdf_close ( array $pdf ) : array
################################################################################

function _pdf_close(& $pdf)
	{
	if(strlen($pdf["filename"]))
		file_put_contents($pdf["filename"], $pdf["stream"]);
	}

################################################################################
# _pdf_concat ( array $pdf , $int $a , int $b , int $c , int $d , int $e , int $f ) : array
################################################################################

function _pdf_concat(& $pdf, $a, $b, $c, $d, $e, $f)
	{
	$pdf["stream"][] = sprintf("%f %f %f %f %f %f cm", $a, $b, $c, $d, $e, $f);
	}

################################################################################
# _pdf_new ( void ) : array
################################################################################

function _pdf_new()
	{
	$pdf = array
		(
		"/ProcSet" => array
			(
			),

		"/Font" => array
			(
			),

		"/XObject" => array
			(
			),

		"objects" => array
			(
			array
				(
				"dictionary" => array
					(
					"/Size" => 0
					)
				)
			),

		"width" => 0,
		"height" => 0,
		"stream" => array
			(
			)
		);

	return($pdf);
	}

################################################################################
# _pdf_end_page ( array $pdf ) : string
################################################################################

function _pdf_end_page(& $pdf)
	{
	$pdf["stream"] = implode(" ", $pdf["stream"]);
	}

################################################################################
# _pdf_get_buffer ( array $pdf ) : string
################################################################################

function _pdf_get_buffer(& $pdf)
	{
	return($pdf["stream"]);
	}

################################################################################
# _pdf_open ( string $filename ) : void
################################################################################

function _pdf_open_file(& $pdf, $filename)
	{
	$pdf["filename"] = $filename;

	if(strlen($pdf["filename"]))
		$pdf["stream"] = file_get_contents($pdf["filename"]);
	}

################################################################################
# _pdf_estore ( array $pdf ) void
################################################################################

function _pdf_restore(& $pdf)
	{
	$pdf["stream"][] = "Q";
	}

################################################################################
# _pdf_rotate ( array $pdf , int $phi ) void
################################################################################

function _pdf_rotate(& $pdf, $phi)
	{
	$sin = sin($phi * M_PI / 180);
	$cos = cos($phi * M_PI / 180);

	_pdf_concat($pdf, 0 + $cos, 0 + $sin, 0 - $sin, 0 + $cos, 0, 0);
	}

################################################################################
# _pdf_save ( array $pdf ) void
################################################################################

function _pdf_save(& $pdf)
	{
	$pdf["stream"][] = "q";
	}

################################################################################
# _pdf_scale ( array $pdf , int $sx , in $sy ) void
################################################################################

function _pdf_scale(& $pdf, $sx, $sy)
	{
	_pdf_concat($pdf, $sx, 0, 0, $sy, 0, 0);
	}

################################################################################
# _pdf_set_font ( array $pdf , string $font , int $size ) void
################################################################################

function _pdf_set_font(& $pdf, $font, $size)
	{
	$pdf["stream"][] = sprintf("%s %d Tf", $font, $size);
	}

################################################################################
# _pdf_set_font ( array $pdf , int $leading ) void
################################################################################

function _pdf_set_leading(& $pdf, $leading)
	{
	$pdf["stream"][] = sprintf("%d TL", $leading);
	}

################################################################################
# _pdf_set_text ( array $pdf , string $text ) void
################################################################################

function _pdf_set_text(& $pdf, $text)
	{
	$pdf["stream"][] = sprintf("(%s) Tj", $text);
	}

################################################################################
# _pdf_set_xy ( array $pdf , int $x , int $y ) void
################################################################################

function _pdf_set_xy(& $pdf, $x, $y)
	{
	$pdf["stream"][] = sprintf("%s %d Td", $x, $y);
	}

################################################################################
# _pdf_translate ( array $pdf , int $tx , int $ty ) void
################################################################################

function _pdf_translate(& $pdf, $tx, $ty)
	{
	_pdf_concat($pdf, 1, 0, 0, 1, $tx, $ty);
	}
?>
