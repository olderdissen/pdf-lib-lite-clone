<?
################################################################################
# copyright 2008 - 2019 by Markus Olderdissen
# free for private use or inspiration. 
# public use need written permission.
################################################################################

define("FONTDESCRIPTOR_FLAG_FIXEDPITCH", 1 << 1);
define("FONTDESCRIPTOR_FLAG_SERIF", 1 << 2);
define("FONTDESCRIPTOR_FLAG_SYMBOLIC", 1 << 3);
define("FONTDESCRIPTOR_FLAG_SCRIPT", 1 << 4);
define("FONTDESCRIPTOR_FLAG_NONSYMBOLIC", 1 << 6);
define("FONTDESCRIPTOR_FLAG_ITALIC", 1 << 7);
define("FONTDESCRIPTOR_FLAG_ALLCAP", 1 << 17);
define("FONTDESCRIPTOR_FLAG_SMALLCAP", 1 << 18);
define("FONTDESCRIPTOR_FLAG_FORCEBOLD", 1 << 19);

################################################################################
# pdf_activate_item - Activate structure element or other content item
# pdf_activate_item ( resource $pdf , int $id ) : bool
# Activates a previously created structure element or other content item.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_activate_item(& $pdf, $id)
	{
	if(sscanf($id, "%d %d R", $id_id, $id_version) != 2)
		die(__FUNCTION__ . ": invalid id: " . $id);

	if(! isset($pdf["objects"][$id_id]))
		die(__FUNCTION__ . ": id not found: " . $id);

	$pdf["active"] = sprintf("%d %d R", $id_id, $id_version);
	}

################################################################################
# pdf_add_annotation - Add annotation [deprecated]
# This function is deprecated, use PDF_create_annotation() with type=Text instead.
################################################################################

function pdf_add_annotation(& $pdf)
	{
	}

################################################################################
# pdf_add_bookmark - Add bookmark for current page [deprecated]
# This function is deprecated since PDFlib version 6, use PDF_create_bookmark() instead.
################################################################################

function pdf_add_bookmark(& $pdf)
	{
	}

################################################################################
# pdf_add_launchlink - Add launch annotation for current page [deprecated]
# pdf_add_launchlink ( resource $pdf , float $llx , float $lly , float $urx , float $ury , string $filename ) : bool
# Adds a link to a web resource.
# This function is deprecated since PDFlib version 6, use PDF_create_action() with type=Launch and PDF_create_annotation() with type=Link instead.
################################################################################

function pdf_add_launchlink(& $pdf, $llx, $lly, $urx, $ury, $filename)
	{
	}

################################################################################
# pdf_add_locallink - Add link annotation for current page [deprecated]
# pdf_add_locallink ( resource $pdf , float $lowerleftx , float $lowerlefty , float $upperrightx , float $upperrighty , int $page , string $dest ) : bool
# Add a link annotation to a target within the current PDF file.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_create_action() with type=GoTo and PDF_create_annotation() with type=Link instead.
################################################################################

function pdf_add_locallink(& $pdf, $lowerleftx, $lowerlefty, $upperrightx, $upperrighty, $page, $dest)
	{
	}

################################################################################
# pdf_add_nameddest - Create named destination
# pdf_add_nameddest ( resource $pdf , string $name , string $optlist ) : bool
# Creates a named destination on an arbitrary page in the current document.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_add_nameddest(& $pdf, $name, $optlist = [])
	{
	}

################################################################################
# pdf_add_note - Set annotation for current page [deprecated]
# pdf_add_note ( resource $pdf , float $llx , float $lly , float $urx , float $ury , string $contents , string $title , string $icon , int $open ) : bool
# Sets an annotation for the current page.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_create_annotation() with type=Text instead.
################################################################################

function pdf_add_note(& $pdf, $llx, $lly, $urx, $ury, $contents, $title, $icon, $open = [])
	{
	}

################################################################################
# pdf_add_outline — Add bookmark for current page [deprecated]
# Add bookmark for current page [deprecated]
# This function is deprecated, use PDF_create_bookmark() instead.
################################################################################

function pdf_add_outline(& $pdf, $text, $parent, $open)
	{
	# check if root exist
	if(! isset($pdf["objects"][0]["dictionary"]["/Root"]))
		die(__FUNCTION__ . ": root not found.");

	# check if root is valid
	if(sscanf($pdf["objects"][0]["dictionary"]["/Root"], "%d %d R", $catalog_id, $catalog_version) != 2)
		die(__FUNCTION__ . ": invalid root.");

	# check if outlines exist
	if(! isset($pdf["objects"][$catalog_id]["dictionary"]["/Outlines"]))
		die(__FUNCTION__ . ": outlines not found.");

	# take default
	if(! $parent)
		$parent = $pdf["objects"][$catalog_id]["dictionary"]["/Outlines"];

	# check if parent is valid
	if(sscanf($parent, "%d %d R", $parent_id, $parent_version) != 2)
		die(__FUNCTION__ . ": invalid outlines: " . $parent);

	# check if open is valid
#	if(sscanf($open, "%d %d R", $open_id, $open_version) != 2)
#		die(__FUNCTION__ . ": invalid open: " . $open);

	# create new object id
	$outline_id = _pdf_get_free_object_id($pdf);
	$outline_version = 0;

	# create new object (outline)
	$pdf["objects"][$outline_id] = [
		"id" => $outline_id,
		"version" => $outline_version,
		"dictionary" => [
			"/Title" => sprintf("(%s)", $text),
			"/Parent" => sprintf("%d %d R", $parent_id, $parent_version)
			]
		];

	# set destination
	if($open)
		$pdf["objects"][$outline_id]["dictionary"]["/Dest"] = sprintf("[%s /Fit]", $open);

	# get counter
	if(isset($pdf["objects"][$parent_id]["dictionary"]["/Count"]))
		$count = $pdf["objects"][$parent_id]["dictionary"]["/Count"];
	else
		$count = 0;

	# this is the first outline ... maybe
	if(! isset($pdf["objects"][$parent_id]["dictionary"]["/First"]))
		$pdf["objects"][$parent_id]["dictionary"]["/First"] = sprintf("%d %d R", $outline_id, $outline_version);

	# modify pointer to last outline
	if(isset($pdf["objects"][$parent_id]["dictionary"]["/Last"]))
		{
		# get previous 
		$last = $pdf["objects"][$parent_id]["dictionary"]["/Last"];

		# check pointer
		if(sscanf($last, "%d %d R", $last_id, $last_version) != 2)
			die(__FUNCTION__ . ": invalid outline: " . $last);

		# this outline is the next one for the previoous outline
		$pdf["objects"][$last_id]["dictionary"]["/Next"] = sprintf("%d %d R", $outline_id, $outline_version);

		# the last outline is the previous now
		$pdf["objects"][$outline_id]["dictionary"]["/Prev"] = $last;
		}

	# this outline is the last one
	$pdf["objects"][$parent_id]["dictionary"]["/Last"] = sprintf("%d %d R", $outline_id, $outline_version);

	# update counter
	$pdf["objects"][$parent_id]["dictionary"]["/Count"] = ($count > 0 ? $count + 1 : $count - 1);

	# return created object id
	return(sprintf("%d %d R", $outline_id, $outline_version));
	}

################################################################################
# pdf_add_pdflink - Add file link annotation for current page [deprecated]
# pdf_add_pdflink ( resource $pdf , float $bottom_left_x , float $bottom_left_y , float $up_right_x , float $up_right_y , string $filename , int $page , string $dest ) : bool
# Add a file link annotation to a PDF target.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_create_action() with type=GoToR and PDF_create_annotation() with type=Link instead.
################################################################################

function pdf_add_pdflink(& $pdf, $bottom_left_x, $bottom_left_y, $up_right_x, $up_right_y, $filename, $page, $dest)
	{
	}

################################################################################
# pdf_add_table_cell - Add a cell to a new or existing table
# pdf_add_table_cell ( resource $pdf , int $table , int $column , int $row , string $text , string $optlist ) : int
# Adds a cell to a new or existing table.
################################################################################

function pdf_add_table_cell(& $pdf, $table, $column, $row, $text, $optlist = [])
	{
	}

################################################################################
# pdf_add_textflow - Create Textflow or add text to existing Textflow
# pdf_add_textflow ( resource $pdf , int $textflow , string $text , string $optlist ) : int
# Creates a Textflow object, or adds text and explicit options to an existing Textflow.
################################################################################

function pdf_add_textflow(& $pdf, $textflow, $text, $optlist = [])
	{
	}

################################################################################
# pdf_add_thumbnail - Add thumbnail for current page
# pdf_add_thumbnail ( resource $pdf , int $image ) : bool
# Adds an existing image as thumbnail for the current page.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_add_thumbnail(& $pdf, $image)
	{
	}

################################################################################
# pdf_add_weblink - Add weblink for current page [deprecated]
# pdf_add_weblink ( resource $pdf , float $lowerleftx , float $lowerlefty , float $upperrightx , float $upperrighty , string $url ) : bool
# Adds a weblink annotation to a target url on the Web.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_create_action() with type=URI and PDF_create_annotation() with type=Link instead.
################################################################################

function pdf_add_weblink(& $pdf, $lowerleftx, $lowerlefty, $upperrightx, $upperrighty, $url)
	{
	}

################################################################################
# pdf_arc - Draw a counterclockwise circular arc segment
# pdf_arc ( resource $pdf , float $x , float $y , float $r , float $alpha , float $beta ) : bool
# Adds a counterclockwise circular arc.
################################################################################

function pdf_arc(& $pdf, $x, $y, $r, $alpha, $beta)
	{
	pdf_arc_orient($pdf, $x, $y, $r, $alpha, $beta, 0 - 1);
	}

################################################################################
# pdf_arcn - Draw a clockwise circular arc segment
# pdf_arcn ( resource $pdf , float $x , float $y , float $r , float $alpha , float $beta ) : bool
# Except for the drawing direction, this function behaves exactly like PDF_arc().
################################################################################

function pdf_arcn(& $pdf, $x, $y, $r, $alpha, $beta)
	{
	pdf_arc_orient($pdf, $x, $y, $r, $alpha, $beta, 0 + 1);
	}

################################################################################

function pdf_arc_orient(& $pdf, $x, $y, $r, $alpha, $beta, $orient)
	{
	$deg_to_rad	= 0.0174532925199433; # pi() / 180

	$rad_a		= $alpha * $deg_to_rad;

	$startx		= ($x + $r * cos($rad_a));
	$starty		= ($y + $r * sin($rad_a));

	pdf_moveto($pdf, $startx, $starty);

	if($orient > 0)
		{
		while($beta < $alpha)
			$beta += 360;

		if($alpha == $beta)
			return;

		while($beta - $alpha > 90)
			{
			pdf_arc_short($pdf, $x, $y, $r, $alpha, $alpha - 90);

			$alpha += 90;
			}
		}
	else
		{
		while($alpha < $beta)
			$alpha += 360;

		if($alpha == $beta)
			return;

		while($alpha - $beta > 90)
			{
			pdf_arc_short($pdf, $x, $y, $r, $alpha, $alpha + 90);

			$alpha -= 90;
			}
		}

	if($alpha != $beta)
		pdf_arc_short($pdf, $x, $y, $r, $alpha, $beta);
	}

################################################################################

function pdf_arc_short(& $pdf, $x, $y, $r, $alpha, $beta)
	{
	$deg_to_rad	= 0.0174532925199433; # pi() / 180

	$alpha		= $alpha * $deg_to_rad;
	$beta		= $beta * $deg_to_rad;

	$bcp		= (4 / 3 * (1 - cos(($beta - $alpha) / 2)) / sin(($beta - $alpha) / 2));

	$sin_apha	= sin($alpha);
	$sin_beta	= sin($beta);
	$cos_alpha	= cos($alpha);
	$cos_beta	= cos($beta);

	pdf_curveto
		(
		$p,
		$x + $r * ($cos_alpha - $bcp * $sin_alpha),
		$y + $r * ($sin_alpha + $bcp * $cos_alpha),
		$x + $r * ($cos_beta + $bcp * $sin_beta),
		$y + $r * ($sin_beta - $bcp * $cos_beta),
		$x + $r * $cos_beta,
		$y + $r * $sin_beta
		);
	}

################################################################################
# pdf_attach_file - Add file attachment for current page [deprecated]
# pdf_attach_file ( resource $pdf , float $llx , float $lly , float $urx , float $ury , string $filename , string $description , string $author , string $mimetype , string $icon ) : bool
# Adds a file attachment annotation.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_create_annotation() with type=FileAttachment instead.
################################################################################

function pdf_attach_file(& $pdf, $llx, $lly, $urx, $ury, $filename, $description, $author, $mimetype, $icon)
	{
	}

################################################################################
# pdf_begin_document - Create new PDF file
# pdf_begin_document ( resource $pdf , string $filename , string $optlist ) : int
# Creates a new PDF file subject to various options.
################################################################################

function pdf_begin_document(& $pdf, $filename, $optlist = [])
	{
	# create new object id
	$catalog_id = _pdf_get_free_object_id($pdf);
	$catalog_version = 0;;

	# create new object (catalog)
	$pdf["objects"][$catalog_id] = [
		"id" => $catalog_id,
		"version" => $catalog_version,
		"dictionary" => [
			"/Type" => "/Catalog",
			"/PageLayout" => "/SinglePage",
			"/PageMode" => "/UseOutlines",
			"/Metadata" => sprintf("%d %d R", 0, 0),
			"/Outlines" => sprintf("%d %d R", 0, 0),
			"/Pages" => sprintf("%d %d R", 0, 0)
			]
		];

	# create xml
	$stream = '<?xpacket?>' .
			'<x:xmpmeta xmlns:x="adobe:ns:meta/">' .
				'<r:RDF xmlns:r="http://www.w3.org/1999/02/22-rdf-syntax-ns#">' .
					'<r:Description xmlns:p="http://www.aiim.org/pdfa/ns/id/">' .
						'<p:part>1</p:part>' .
						'<p:conformance>A</p:conformance>' .
					'</r:Description>' .
				'</r:RDF>' .
			'</x:xmpmeta>' .
		'<?xpacket?>';

	# create new object id
	$metadata_id = _pdf_get_free_object_id($pdf);
	$metadata_version = 0;;

	# create new object (metadata)
	$pdf["objects"][$metadata_id] = [
		"id" => $metadata_id,
		"version" => $metadata_version,
		"dictionary" => [
			"/Type" => "/Metadata",
			"/Subtype" => "/XML",
			"/Length" => strlen($stream)
			],
		"stream" => $stream
		];

	# create new object id
	$outlines_id = _pdf_get_free_object_id($pdf);
	$outlines_version = 0;;

	# create new object (outlines)
	$pdf["objects"][$outlines_id] = [
		"id" => $outlines_id,
		"version" => $outlines_version,
		"dictionary" => [
			"/Type" => "/Outlines",
			"/Count" => 0
			]
		];

	# create new object id
	$pages_id = _pdf_get_free_object_id($pdf);
	$pages_version = 0;

	# create new object (pages)
	$pdf["objects"][$pages_id] = [
		"id" => $pages_id,
		"version" => $pages_version,
		"dictionary" => [
			"/Type" => "/Pages",
			"/Kids" => "[]",
			"/Count" => 0
			]
		];

	# apply location of metadata
	$pdf["objects"][$catalog_id]["dictionary"]["/Metadata"] = sprintf("%d %d R", $metadata_id, $metadata_version);

	# apply location of outlines to catalog
	$pdf["objects"][$catalog_id]["dictionary"]["/Outlines"] = sprintf("%d %d R", $outlines_id, $outlines_version);

	# apply location of pages to catalog
	$pdf["objects"][$catalog_id]["dictionary"]["/Pages"] = sprintf("%d %d R", $pages_id, $pages_version);

	# apply location of catalog
	$pdf["objects"][0]["dictionary"]["/Root"] = sprintf("%d %d R", $catalog_id, $catalog_version);

	# add additional help
	$pdf["filename"] = $filename;
	$pdf["resources"] = [];
	}

################################################################################
# pdf_begin_font - Start a Type 3 font definition
# pdf_begin_font ( resource $pdf , string $filename , float $a , float $b , float $c , float $d , float $e , float $f , string $optlist ) : bool
# Starts a Type 3 font definition.
################################################################################

function pdf_begin_font(& $pdf, $filename, $a, $b, $c, $d, $e, $f, $optlist = [])
	{
	}

################################################################################
# pdf_begin_glyph - Start glyph definition for Type 3 font
# pdf_begin_glyph ( resource $pdf , string $glyphname , float $wx , float $llx , float $lly , float $urx , float $ury ) : bool
# Starts a glyph definition for a Type 3 font.
################################################################################

function pdf_begin_glyph(& $pdf, $glyphname, $wx, $llx, $lly, $urx, $ury)
	{
	}

################################################################################
# pdf_begin_item - Open structure element or other content item
# pdf_begin_item ( resource $pdf , string $tag , string $optlist ) : int
# Opens a structure element or other content item with attributes supplied as options.
################################################################################

function pdf_begin_item(& $pdf, $tag, $optlist = [])
	{
	}

################################################################################
# pdf_begin_layer - Start layer
# pdf_begin_layer ( resource $pdf , int $layer ) : bool
# Starts a layer for subsequent output on the page.
# Returns TRUE on success or FALSE on failure.
# This function requires PDF 1.5.
################################################################################

function pdf_begin_layer(& $pdf, $layer)
	{
	}

################################################################################
# pdf_begin_page - Start new page [deprecated]
# pdf_begin_page ( resource $pdf , float $width , float $height ) : bool
# Adds a new page to the document.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_begin_page_ext() instead.
################################################################################

function pdf_begin_page(& $pdf, $width, $height)
	{
	pdf_begin_page_ext($pdf, $width, $height);
	}

################################################################################
# pdf_begin_page_ext - Start new page
# pdf_begin_page_ext ( resource $pdf , float $width , float $height , string $optlist ) : bool
# Adds a new page to the document, and specifies various options.
# The parameters width and height are the dimensions of the new page in points.
# Returns TRUE on success or FALSE on failure.
################################################################################
# Common Page Sizes in Points
# 	name			size
# 	A0			2380 x 3368
# 	A1			1684 x 2380
# 	A2			1190 x 1684
# 	A3			842 x 1190
# 	A4			595 x 842
# 	A5			421 x 595
# 	A6			297 x 421
# 	B5			501 x 709
# 	letter (8.5" x 11")	612 x 792
# 	legal (8.5" x 14")	612 x 1008
# 	ledger (17" x 11")	1224 x 792
# 	11" x 17"		792 x 1224
################################################################################

function pdf_begin_page_ext(& $pdf, $width, $height, $optlist = [])
	{
	# check if root exist
	if(! isset($pdf["objects"][0]["dictionary"]["/Root"]))
		die(__FUNCTION__ . ": root not found.");

	# check if root is valid
	if(sscanf($pdf["objects"][0]["dictionary"]["/Root"], "%d %d R", $catalog_id, $catalog_version) != 2)
		die(__FUNCTION__ . ": invalid root.");

	# check if pages exist
	if(! isset($pdf["objects"][$catalog_id]["dictionary"]["/Pages"]))
		die(__FUNCTION__ . ": pages not found.");

	# check parent
	if(isset($optlist["parent"]))
		$pages = $optlist["parent"];
	else
		$pages = $pdf["objects"][$catalog_id]["dictionary"]["/Pages"];

	# get parent id
	if(sscanf($pages, "%d %d R", $pages_id, $pages_version) != 2)
		die(__FUNCTION__ . ": invalid parent.");

	# apply page
	$page_id = _pdf_get_free_object_id($pdf);
	$page_version = 0;

	# create new object (page)
	$pdf["objects"][$page_id] = [
		"id" => $page_id,
		"version" => $page_version,
		"dictionary" => [
			"/Type" => "/Page",
			"/Parent" => $pages,
			"/Resources" => ["/ProcSet" => ["/PDF", "/Text"]],
			"/MediaBox" => sprintf("[%d %d %d %d]", 0, 0 , $width, $height),
			"/Contents" => sprintf("%d %d R", 0, 0)
			]
		];

	# apply duration
	if(isset($optlist["duration"]))
		$pdf["objects"][$page_id]["dictionary"]["/Dur"] = $optlist["duration"];

	# get count
	if(isset($pdf["objects"][$pages_id]["dictionary"]["/Count"]))
		$count = $pdf["objects"][$pages_id]["dictionary"]["/Count"];
	else
		$count = 0;

	# get kids
	if(isset($pdf["objects"][$pages_id]["dictionary"]["/Kids"]))
		$data = $pdf["objects"][$pages_id]["dictionary"]["/Kids"];
	else
		$data = "[]";

	# parse kids
	$data = substr($data, 1);
	list($kids, $data) = _pdf_parse_array($data);
	$data = substr($data, 1);

	# apply page to kids
	$kids[] = sprintf("%d %d R", $page_id, $page_version);

	# apply kids
	$pdf["objects"][$pages_id]["dictionary"]["/Kids"] = sprintf("[%s]", _pdf_glue_array($kids));

	# increase counter
	$pdf["objects"][$pages_id]["dictionary"]["/Count"] = $count + 1;

	# update internals
	$pdf["active"] = sprintf("%d %d R", $page_id, $page_version);
	$pdf["stream"] = [];

	# return created object id
	return(sprintf("%d %d R", $page_id, $page_version));
	}

################################################################################
# pdf_begin_pattern - Start pattern definition
# pdf_begin_pattern ( resource $pdf , float $width , float $height , float $xstep , float $ystep , int $painttype ) : int
# Starts a new pattern definition.
################################################################################

function pdf_begin_pattern(& $pdf, $width, $height, $xstep, $ystep, $painttype)
	{
	}

################################################################################
# pdf_begin_template_ext - Start template definition
# pdf_begin_template_ext ( resource $pdf , float $width , float $height , string $optlist ) : int
# Starts a new template definition.
################################################################################

function pdf_begin_template_ext(& $pdf, $width, $height, $optlist = [])
	{
	}

################################################################################
# pdf_begin_template - Start template definition [deprecated]
# pdf_begin_template ( resource $pdf , float $width , float $height ) : int
# Starts a new template definition.
# This function is deprecated since PDFlib version 7, use PDF_begin_template_ext() instead.
################################################################################

function pdf_begin_template(& $pdf, $width, $height)
	{
	pdf_begin_template_ext($pdf, $width, $height);
	}

################################################################################
# pdf_circle - Draw a circle
# pdf_circle ( resource $pdf , float $x , float $y , float $r ) : bool
# Adds a circle.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_circle(& $pdf, $x, $y, $r)
	{
	#$arc_magic = 4 / 3 * (M_SQRT2 - 1);
	$arc_magic = 0.552284749;

	pdf_moveto($pdf, $x + $r, $y);
	pdf_curveto($pdf, $x + $r, $y + $r * $arc_magic, $x + $r * $arc_magic, $y + $r, $x, $y + $r);
	pdf_curveto($pdf, $x - $r * $arc_magic, $y + $r, $x - $r, $y + $r * $arc_magic, $x - $r, $y);
	pdf_curveto($pdf, $x - $r, $y - $r * $arc_magic, $x - $r * $arc_magic, $y - $r, $x, $y - $r);
	pdf_curveto($pdf, $x + $r * $arc_magic, $y - $r, $x + $r, $y - $r * $arc_magic, $x + $r, $y);
	}

################################################################################
# pdf_clip - Clip to current path
# pdf_clip ( resource $pdf ) : bool
# Uses the current path as clipping path, and terminate the path.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_clip(& $pdf)
	{
	$pdf["stream"][] = "W";
	}

################################################################################
# pdf_close - Close pdf resource [deprecated]
# pdf_close ( resource $pdf ) : bool
# Closes the generated PDF file, and frees all document-related resources.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_end_document() instead.
################################################################################

function pdf_close(& $pdf)
	{
	pdf_end_document($pdf);
	}

################################################################################
# pdf_close_image - Close image
# pdf_close_image ( resource $pdf , int $image ) : bool
# Closes an image retrieved with the PDF_open_image() function.
################################################################################

function pdf_close_image(& $pdf, $image)
	{
	$pdf["stream"][] = "EI";
	}

################################################################################
# pdf_close_pdi_page - Close the page handle
# pdf_close_pdi_page ( resource $pdf , int $page ) : bool
# Closes the page handle, and frees all page-related resources.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_close_pdi_page(& $pdf, $page)
	{
	}

################################################################################
# pdf_close_pdi_document - Close the document handle
# pdf_close_pdi_document ( resource $pdf , int $doc ) : bool
# Closes all open page handles, and closes the input PDF document.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_close_pdi_document(& $pdf, $doc)
	{
	}

################################################################################
# pdf_close_pdi - Close the input PDF document [deprecated]
# pdf_close_pdi ( resource $pdf , int $doc ) : bool
# Closes all open page handles, and closes the input PDF document.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 7, use PDF_close_pdi_document() instead.
################################################################################

function pdf_close_pdi(& $pdf, $doc)
	{
	pdf_close_pdi_document($pdf);
	}

################################################################################
# pdf_closepath - Close current path
# pdf_closepath ( resource $pdf ) : bool
# Closes the current path.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_closepath(& $pdf)
	{
	$pdf["stream"][] = "h";
	}

################################################################################
# pdf_closepath_fill_stroke - Close, fill and stroke current path
# pdf_closepath_fill_stroke ( resource $pdf ) : bool
# Closes the path, fills, and strokes it.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_closepath_fill_stroke(& $pdf)
	{
	$pdf["stream"][] = "b";
	}

################################################################################
# pdf_closepath_stroke - Close and stroke path
# pdf_closepath_stroke ( resource $pdf ) : bool
# Closes the path, and strokes it.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_closepath_stroke(& $pdf)
	{
	$pdf["stream"][] = "s";
	}

################################################################################
# pdf_concat - Concatenate a matrix to the CTM
# pdf_concat ( resource $pdf , float $a , float $b , float $c , float $d , float $e , float $f ) : bool
# Concatenates a matrix to the current transformation matrix (CTM).
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_concat(& $pdf, $a, $b, $c, $d, $e, $f)
	{
	$pdf["stream"][] = sprintf("%f %f %f %f %f %f cm", $a, $b, $c, $d, $e, $f);
	}

################################################################################
# pdf_continue_text - Output text in next line
# pdf_continue_text ( resource $pdf , string $text ) : bool
# Prints text at the next line.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_continue_text(& $pdf, $text)
	{
	# used by pdf_show_boxed:
	#  BT will set
	#  pdf_set_textpos will be used
	#  pdf_show and pdf_continue_text will be used
	#  ET will be set

	$pdf["stream"][] = "T*";

	# check text
	if(! strlen($text))
		return;

	# remove disturbing characters
	$text = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);

	$pdf["stream"][] = sprintf("(%s) Tj", $text);
	}

################################################################################
# pdf_create_3dview - Create 3D view
# pdf_create_3dview ( resource $pdf , string $username , string $optlist ) : int
# Creates a 3D view.
# This function requires PDF 1.6.
################################################################################

function pdf_create_3dview(& $pdf, $username, $optlist = [])
	{
	}

################################################################################
# pdf_create_action - Create action for objects or events
# pdf_create_action ( resource $pdf , string $type , string $optlist ) : int
# Creates an action which can be applied to various objects and events.
################################################################################

function pdf_create_action(& $pdf, $type, $optlist = [])
	{
	# check type
	if(! in_array($type, ["GoTo", "GoToR", "Launch", "uri"]))
		die(__FUNCTION__ . ": invalid type: " . $type);

	# create new object id
	$action_id = _pdf_get_free_object_id($pdf);
	$action_version = 0;;

	if($type == "GoTo")
		{
		# create new object (action)
		$pdf["objects"][$action_id] = [
			"id" => $action_id,
			"version" => $action_version,
			"dictionary" => [
				"/Type" => "/Action",
				"/S" => "/GoTo"
				]
			];

		# apply destination
		if(isset($optlist["dest"]))
			$pdf["objects"][$action_id]["dictionary"]["/D"] = sprintf("[%s /Fit]", $optlist["dest"]);
		}

	if($type == "GoToR")
		{
		# create new object (action)
		$pdf["objects"][$action_id] = [
			"id" => $action_id,
			"version" => $action_version,
			"dictionary" => [
				"/Type" => "/Action",
				"/S" => "/GoToR"
				]
			];

		# apply filename
		if(isset($optlist["filename"]))
			$pdf["objects"][$action_id]["dictionary"]["/F"] = sprintf("(%s)", $optlist["filename"]);

		# apply destination
		if(isset($optlist["dest"]))
			$pdf["objects"][$action_id]["dictionary"]["/D"] = sprintf("[%s /Fit]", $optlist["dest"]);
		}

	if($type == "Launch")
		{
		# create new object (action)
		$pdf["objects"][$action_id] = [
			"id" => $action_id,
			"version" => $action_version,
			"dictionary" => [
				"/Type" => "/Action",
				"/S" => "/Launch"
				]
			];

		# apply filename
		if(isset($optlist["filename"]))
			$pdf["objects"][$action_id]["dictionary"]["/F"] = sprintf("(%s)", $optlist["filename"]);
		}

	if($type == "uri")
		{
		# create new object (action)
		$pdf["objects"][$action_id] = [
			"id" => $action_id,
			"version" => $action_version,
			"dictionary" => [
				"/Type" => "/Action",
				"/S" => "/URI"
				]
			];

		# apply uri
		if(isset($optlist["uri"]))
			$pdf["objects"][$action_id]["dictionary"]["/URI"] = sprintf("(%s)", $optlist["uri"]);
		}

	# return created object id
	return(sprintf("%d %d R", $action_id, $action_version));
	}

################################################################################
# pdf_create_annotation - Create rectangular annotation
# pdf_create_annotation ( resource $pdf , float $llx , float $lly , float $urx , float $ury , string $type , string $optlist ) : bool
# Creates a rectangular annotation on the current page.
################################################################################

function pdf_create_annotation(& $pdf, $llx, $lly, $urx, $ury, $type, $optlist = [])
	{
#	if(sscanf($parent, "%d %d R", $parent_id, $parent_version) != 2)
#		die(__FUNCTION__ . ": invalid parent: " . $parent);

	# check type
	if(! in_array($type, ["Attachment", "Link", "Text", "widget"]))
		die(__FUNCTION__ . ": invalid type: " . $type);

	# create new object id
	$annotation_id = _pdf_get_free_object_id($pdf);
	$annotation_version = 0;;

	if($type == "Attachment")
		{
		# create new object (annotation)
		$pdf["objects"][$annotation_id] = [
			"id" => $annotation_id,
			"version" => $annotation_version,
			"dictionary" => [
				"/Type" => "/Annot",
				"/Subtype" => "/Attachment",
				"/Rect" => sprintf("[%d %d %d %d]", $llx, $lly, $urx, $ury)
				]
			];
		}

	if($type == "Link")
		{
		# create new object (annotation)
		$pdf["objects"][$annotation_id] = [
			"id" => $annotation_id,
			"version" => $annotation_version,
			"dictionary" => [
				"/Type" => "/Annot",
				"/Subtype" => "/Link",
				"/Rect" => sprintf("[%d %d %d %d]", $llx, $lly, $urx, $ury)
				]
			];

		# apply action
		if(isset($optlist["action"]))
			$pdf["objects"][$annotation_id]["dictionary"]["/A"] = $optlist["action"];

		# apply dash-array
		if(isset($optlist["dasharray"]))
			$pdf["objects"][$annotation_id]["dictionary"]["/Border"] = $optlist["dasharray"];
		}

	if($type == "Text")
		{
		# create new object (annotation)
		$pdf["objects"][$annotation_id] = [
			"id" => $annotation_id,
			"version" => $annotation_version,
			"dictionary" => [
				"/Type" => "/Annot",
				"/Subtype" => "/Text",
				"/Rect" => sprintf("[%d %d %d %d]", $llx, $lly, $urx, $ury)
				]
			];

		# apply title
		if(isset($optlist["title"]))
			$pdf["objects"][$annotation_id]["dictionary"]["/Contents"] = sprintf("(%s)", $optlist["title"]);
		}

	# check if page is valid
	if(sscanf($pdf["active"], "%d %d R", $page_id, $page_version) != 2)
		die(__FUNCTION__ . ": invalid page.");

	# get annotations
	if(isset($pdf["objects"][$page_id]["dictionary"]["/Annots"]))
		$data = $pdf["objects"][$page_id]["dictionary"]["/Annots"];
	else
		$data = "[]";

	# parse annotation
	$data = substr($data, 1);
	list($annots, $data) = _pdf_parse_array($data);
	$data = substr($data, 1);

	# apply annotation to annotations
	$annots[] = sprintf("%d %d R", $annotation_id, $annotation_version);

	# apply annotations
	$pdf["objects"][$page_id]["dictionary"]["/Annots"] = sprintf("[%s]", _pdf_glue_array($annots));

	# return created object id
	return(sprintf("%d %d R", $annotation_id, $annotation_version));
	}

################################################################################
# pdf_create_bookmark - Create bookmark
# pdf_create_bookmark ( resource $pdf , string $text , string $optlist ) : int
# Creates a bookmark subject to various options.
################################################################################

function pdf_create_bookmark(& $pdf, $text, $optlist = [])
	{
	}

################################################################################
# pdf_create_field - Create form field
# pdf_create_field ( resource $pdf , float $llx , float $lly , float $urx , float $ury , string $name , string $type , string $optlist ) : bool
# Creates a form field on the current page subject to various options.
################################################################################

function pdf_create_field(& $pdf, $llx, $lly, $urx, $ury, $name, $type, $optlist = [])
	{
	}

################################################################################
# pdf_create_fieldgroup - Create form field group
# pdf_create_fieldgroup ( resource $pdf , string $name , string $optlist ) : bool
# Creates a form field group subject to various options.
################################################################################

function pdf_create_fieldgroup(& $pdf, $name, $optlist = [])
	{
	}

################################################################################
# pdf_create_gstate - Create graphics state object
# pdf_create_gstate ( resource $pdf , string $optlist ) : int
# Creates a graphics state object subject to various options.
################################################################################

function pdf_create_gstate(& $pdf, $optlist = [])
	{
	}

################################################################################
# pdf_create_pvf - Create PDFlib virtual file
# pdf_create_pvf ( resource $pdf , string $filename , string $data , string $optlist ) : bool
# Creates a named virtual read-only file from data provided in memory.
################################################################################

function pdf_create_pvf(& $pdf, $filename, $data, $optlist = [])
	{
	}

################################################################################
# pdf_create_textflow - Create textflow object
# pdf_create_textflow ( resource $pdf , string $text , string $optlist ) : int
# Preprocesses text for later formatting and creates a textflow object.
################################################################################

function pdf_create_textflow(& $pdf, $text, $optlist = [])
	{
	}

################################################################################
# pdf_curveto - Draw Bezier curve
# pdf_curveto ( resource $pdf , float $x1 , float $y1 , float $x2 , float $y2 , float $x3 , float $y3 ) : bool
# Draws a Bezier curve from the current point, using 3 more control points.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_curveto(& $pdf, $x1, $y1, $x2, $y2, $x3, $y3)
	{
	$pdf["stream"][] = sprintf("%f %f %f %f %f %f c", $x1, $y1, $x2, $y2, $x3, $y3);
	}

################################################################################
# pdf_define_layer - Create layer definition
# pdf_define_layer ( resource $pdf , string $name , string $optlist ) : int
# Creates a new layer definition.
# This function requires PDF 1.5.
################################################################################

function pdf_define_layer(& $pdf, $name, $optlist = [])
	{
	}

################################################################################
# pdf_delete_pvf - Delete PDFlib virtual file
# pdf_delete_pvf ( resource $pdf , string $filename ) : int
# Deletes a named virtual file and frees its data structures (but not the contents).
################################################################################

function pdf_delete_pvf(& $pdf, $filename)
	{
	}

################################################################################
# pdf_delete_table - Delete table object
# pdf_delete_table ( resource $pdf , int $table , string $optlist ) : bool
# Deletes a table and all associated data structures.
################################################################################

function pdf_delete_table(& $pdf, $table, $optlist = [])
	{
	}

################################################################################
# pdf_delete_textflow - Delete textflow object
# pdf_delete_textflow ( resource $pdf , int $textflow ) : bool
# Deletes a textflow and the associated data structures.
################################################################################

function pdf_delete_textflow(& $pdf, $textflow)
	{
	}

################################################################################
# pdf_delete - Delete PDFlib object
# pdf_delete ( resource $pdf ) : bool
# Deletes a PDFlib object, and frees all internal resources.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_delete(& $pdf)
	{
	$pdf = null;
	}

################################################################################
# pdf_encoding_set_char - Add glyph name and/or Unicode value
# pdf_encoding_set_char ( resource $pdf , string $encoding , int $slot , string $glyphname , int $uv ) : bool
# Adds a glyph name and/or Unicode value to a custom encoding.
################################################################################

function pdf_encoding_set_char(& $pdf, $encoding, $slot, $glyphname, $uv)
	{
	}

################################################################################
# pdf_end_document - Close PDF file
# pdf_end_document ( resource $pdf , string $optlist ) : bool
# Closes the generated PDF file and applies various options.
################################################################################

function pdf_end_document(& $pdf, $optlist = [])
	{
	# create new object id
	$info_id = _pdf_get_free_object_id($pdf);
	$info_version = 0;;

	# create new object (info)
	$pdf["objects"][$info_id] = [
		"id" => $info_id,
		"version" => $info_version,
		"dictionary" => [
			"/CreationDate" => sprintf("(D:%sZ)", date("YmdHis")),
			"/ModDate" => sprintf("(D:%sZ)", date("YmdHis")),
			"/Producer" => sprintf("(%s)", basename(__FILE__))
			]
		];

	# remove widths on core fonts
	if(isset($pdf["resources"]["/Font"]))
		foreach($pdf["resources"]["/Font"] as $index => $object)
			{
			# check resource pointer
			if(sscanf($object, "%d %d R", $object_id, $object_version) != 2)
				die(__FUNCTION__ . ": invalid object: " . $object);

			# check if subtype exist
			if(! isset($pdf["objects"][$object_id]["dictionary"]["/Subtype"]))
				die(__FUNCTION__ . ": subtype not found.");

			# check if widths exist
			if(! isset($pdf["objects"][$object_id]["dictionary"]["/Widths"]))
				die(__FUNCTION__ . ": widths not found.");

			# remove withs on type1 fonts and glue withs on all other fonts
			if($pdf["objects"][$object_id]["dictionary"]["/Subtype"] != "/Type1")
				$pdf["objects"][$object_id]["dictionary"]["/Widths"] = sprintf("[%s]", _pdf_glue_array($pdf["objects"][$object_id]["dictionary"]["/Widths"]));
			else
				foreach(["/FirstChar", "/LastChar", "/Widths"] as $key)
					if(isset($pdf["objects"][$object_id]["dictionary"][$key]))
						unset($pdf["objects"][$object_id]["dictionary"][$key]);

			}
			
	# apply additional settings to info object
	if(isset($pdf["info"]))
		foreach($pdf["info"] as $key => $value)
			$pdf["objects"][$info_id]["dictionary"][$key] = $value;

	# apply location of info
	$pdf["objects"][0]["dictionary"]["/Info"] = sprintf("%d %d R", $info_id, $info_version);
	
	# apply some filter
	_pdf_filter_change($pdf, "/FlateDecode");

	# glue all objects
	$pdf["stream"] = _pdf_glue_document($pdf["objects"]);

	# store to disk if filename was set in pdf_begin_document
	if($pdf["filename"])
		file_put_contents($pdf["filename"], $pdf["stream"]);
	}

################################################################################
# pdf_end_font - Terminate Type 3 font definition
# pdf_end_font ( resource $pdf ) : bool
# Terminates a Type 3 font definition.
################################################################################

function pdf_end_font(& $pdf)
	{
	}

################################################################################
# pdf_end_glyph - Terminate glyph definition for Type 3 font
# pdf_end_glyph ( resource $pdf ) : bool
# Terminates a glyph definition for a Type 3 font.
################################################################################

function pdf_end_glyph(& $pdf)
	{
	}

################################################################################
# pdf_end_item - Close structure element or other content item
# pdf_end_item ( resource $pdf , int $id ) : bool
# Closes a structure element or other content item.
################################################################################

function pdf_end_item(& $pdf, $id)
	{
	}

################################################################################
# pdf_end_layer - Deactivate all active layers
# pdf_end_layer ( resource $pdf ) : bool
# Deactivates all active layers.
# Returns TRUE on success or FALSE on failure.
# This function requires PDF 1.5.
################################################################################

function pdf_end_layer(& $pdf)
	{
	}

################################################################################
# pdf_end_page - Finish page
# pdf_end_page ( resource $pdf ) : bool
# Finishes the page.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_end_page(& $pdf)
	{
	return(pdf_end_page_ext($pdf));
	}

################################################################################
# pdf_end_page_ext - Finish page
# pdf_end_page_ext ( resource $pdf , string $optlist ) : bool
# Finishes a page, and applies various options.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_end_page_ext(& $pdf, $optlist = [])
	{
	# check page
	if(sscanf($pdf["active"], "%d %d R", $page_id, $page_version) != 2)
		die(__FUNCTION__ . ": invalid page.");

	# glue resources (arrays)
	foreach(["/ProcSet"] as $type)
		if(isset($pdf["objects"][$page_id]["dictionary"]["/Resources"][$type]))
			$pdf["objects"][$page_id]["dictionary"]["/Resources"][$type] = sprintf("[%s]", _pdf_glue_array($pdf["objects"][$page_id]["dictionary"]["/Resources"][$type]));

	# glue resouces (dictionaries)
	foreach(["/Font", "/XObject"] as $type)
		if(isset($pdf["objects"][$page_id]["dictionary"]["/Resources"][$type]))
			$pdf["objects"][$page_id]["dictionary"]["/Resources"][$type] = sprintf("<< %s >>", _pdf_glue_dictionary($pdf["objects"][$page_id]["dictionary"]["/Resources"][$type]));

	# apply group
	if($pdf["minor"] > 3)
		$pdf["objects"][$page_id]["dictionary"]["/Group"] = "<< /Type /Group /S /Transparency /CS /DeviceRGB >>";

	# apply duration
	if(isset($optlist["duration"]))
		$pdf["objects"][$page_id]["dictionary"]["/Dur"] = $optlist["duration"];

	# apply emply content
	$pdf["objects"][$page_id]["dictionary"]["/Contents"] = _pdf_add_stream($pdf, implode(PHP_EOL, $pdf["stream"]));

	# return page id
	return($pdf["active"]);
	}

################################################################################
# pdf_end_pattern - Finish pattern
# pdf_end_pattern ( resource $pdf ) : bool
# Finishes the pattern definition.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_end_pattern(& $pdf)
	{
	}

################################################################################
# pdf_end_template - Finish template
# pdf_end_template ( resource $pdf ) : bool
# Finishes a template definition.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_end_template(& $pdf)
	{
	}

################################################################################
# pdf_endpath - End current path
# pdf_endpath ( resource $pdf ) : bool
# Ends the current path without filling or stroking it.
################################################################################

function pdf_endpath(& $pdf)
	{
	$pdf["stream"][] = "n";
	}
	
################################################################################
# pdf_fill - Fill current path
# pdf_fill ( resource $pdf ) : bool
# Fills the interior of the current path with the current fill color.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_fill(& $pdf)
	{
	$pdf["stream"][] = "f";
	}

################################################################################
# pdf_fill_imageblock - Fill image block with variable data
# pdf_fill_imageblock ( resource $pdf , int $page , string $blockname , int $image , string $optlist ) : int
# Fills an image block with variable data according to its properties.
# This function is only available in the PDFlib Personalization Server (PPS).
################################################################################

function pdf_fill_imageblock(& $pdf, $page, $blockname, $image, $optlist = [])
	{
	}

################################################################################
# pdf_fill_pdfblock - Fill PDF block with variable data
# pdf_fill_pdfblock ( resource $pdf , int $page , string $blockname , int $contents , string $optlist ) : int
# Fills a PDF block with variable data according to its properties.
# This function is only available in the PDFlib Personalization Server (PPS).
################################################################################

function pdf_fill_pdfblock(& $pdf, $page, $blockname, $contents, $optlist = [])
	{
	}

################################################################################
# pdf_fill_stroke - Fill and stroke path
# pdf_fill_stroke ( resource $pdf ) : bool
# Fills and strokes the current path with the current fill and stroke color.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_fill_stroke(& $pdf)
	{
	$pdf["stream"][] = "B";
	}

################################################################################
# pdf_fill_textblock - Fill text block with variable data
# pdf_fill_textblock ( resource $pdf , int $page , string $blockname , string $text , string $optlist ) : int
# Fills a text block with variable data according to its properties.
# This function is only available in the PDFlib Personalization Server (PPS).
################################################################################

function pdf_fill_textblock(& $pdf, $page, $blockname, $text, $optlist = [])
	{
	}

################################################################################
# pdf_findfont - Prepare font for later use [deprecated]
# pdf_findfont ( resource $pdf , string $fontname , string $encoding , int $embed ) : int
# Search for a font and prepare it for later use with PDF_setfont().
# The metrics will be loaded, and if embed is nonzero, the font file will be checked, but not yet used.
# encoding is one of builtin, macroman, winansi, host, a user-defined encoding name or the name of a CMap.
# Parameter embed is optional before PHP 4.3.5 or with PDFlib less than 5.
# This function is deprecated since PDFlib version 5, use PDF_load_font() instead.
################################################################################

function pdf_findfont(& $pdf, $fontname, $encoding = "builtin", $embed = 0)
	{
#	printf(__FUNCTION__ . ": %s\n", $fontname);

	# check encoding
	if(! in_array($encoding, ["builtin", "winansi", "macroman", "macexpert"]))
		die(__FUNCTION__ . ": invalid encoding: " . $encoding);

	$font = "";

	if(isset($pdf["resources"]["/Font"]))
		foreach($pdf["resources"]["/Font"] as $index => $object)
			if(sscanf($object, "%d %d R", $object_id, $object_version) == 2)
				if($pdf["objects"][$object_id]["dictionary"]["/BaseFont"] == "/" . $fontname)
					$font = $index;

	if(! $font)
		if($embed)
			return(pdf_load_font($pdf, $fontname, $encoding, $embed));
		else
			die(__FUNCTION__ . ": font not found:" . $fontname);

	# return font id
	return($font);
	}

################################################################################
# pdf_fit_image - Place image or template
# pdf_fit_image ( resource $pdf , int $image , float $x , float $y , string $optlist ) : bool
# Places an image or template on the page, subject to various options.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_fit_image(& $pdf, $image, $x, $y, $optlist = [])
	{
	# check image
	if(sscanf($image, "/X%d", $whatever) != 1)
		die(__FUNCTION__ . ": invalid image: " . $image);

	# check existence
	if(! isset($pdf["resources"]["/XObject"][$image]))
		die(__FUNCTION__ . ": no images loaded: " . $image);

	# check pointer
	if(sscanf($pdf["resources"]["/XObject"][$image], "%d %d R", $object_id, $object_version) != 2)
		die(__FUNCTION__ . ": invalid image: " . $image);

	# check pointer
	if(sscanf($pdf["active"], "%d %d R", $page_id, $page_version) != 2)
		die(__FUNCTION__ . ": invalid page.");

	# remember image as used resource
	$pdf["objects"][$page_id]["dictionary"]["/Resources"]["/XObject"][$image] = $pdf["resources"]["/XObject"][$image];

	# get dimensions of image
	$w = $pdf["objects"][$object_id]["dictionary"]["/Width"];
	$h = $pdf["objects"][$object_id]["dictionary"]["/Height"];

	# check colorspace
	if(sscanf($pdf["objects"][$object_id]["dictionary"]["/ColorSpace"], "[%s %s %d %s]", $a, $b, $c, $d) != 4)
		list($a, $b, $c, $d) = ["", $pdf["objects"][$object_id]["dictionary"]["/ColorSpace"], "", ""];

	# update procset (gray images)
	if($b == "/DeviceGray")
		if(! in_array("/ImageB", $pdf["objects"][$page_id]["dictionary"]["/Resources"]["/ProcSet"]))
			$pdf["objects"][$page_id]["dictionary"]["/Resources"]["/ProcSet"][] = "/ImageB";

	# update procset (colored images)
	if($b == "/DeviceRGB")
		if(! in_array("/ImageC", $pdf["objects"][$page_id]["dictionary"]["/Resources"]["/ProcSet"]))
			$pdf["objects"][$page_id]["dictionary"]["/Resources"]["/ProcSet"][] = "/ImageC";

	# update procset (indexed images)
	if($a == "/Indexed")
		if(! in_array("/ImageI", $pdf["objects"][$page_id]["dictionary"]["/Resources"]["/ProcSet"]))
			$pdf["objects"][$page_id]["dictionary"]["/Resources"]["/ProcSet"][] = "/ImageI";

	# pdf-api of firefox throughs error if cm uses float (f) ... setlocale(de_de) makes trouble
	$pdf["stream"][] = "q";
	$pdf["stream"][] = sprintf("%f %f %f %f %f %f cm", $w * $optlist["scale"], 0, 0, $h * $optlist["scale"], $x, $y);
	$pdf["stream"][] = sprintf("%s Do", $image); # Invoke named XObject
	$pdf["stream"][] = "Q";
	}

################################################################################
# pdf_fit_pdi_page - Place imported PDF page
# pdf_fit_pdi_page ( resource $pdf , int $page , float $x , float $y , string $optlist ) : bool
# Places an imported PDF page on the page, subject to various options.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_fit_pdi_page(& $pdf, $page, $x, $y, $optlist = [])
	{
	}

################################################################################
# pdf_fit_table - Place table on page
# pdf_fit_table ( resource $pdf , int $table , float $llx , float $lly , float $urx , float $ury , string $optlist ) : string
# Places a table on the page fully or partially.
################################################################################

function pdf_fit_table(& $pdf, $table, $llx, $lly, $urx, $ury, $optlist = [])
	{
	}

################################################################################
# pdf_fit_textflow - Format textflow in rectangular area
# pdf_fit_textflow ( resource $pdf , int $textflow , float $llx , float $lly , float $urx , float $ury , string $optlist ) : string
# Formats the next portion of a textflow into a rectangular area.
################################################################################

function pdf_fit_textflow(& $pdf, $text, $llx, $lly, $urx, $ury, $optlist = [])
	{
	}

################################################################################
# pdf_fit_textline - Place single line of text
# pdf_fit_textline ( resource $pdf , string $text , float $x , float $y , string $optlist ) : bool
# Places a single line of text on the page, subject to various options.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_fit_textline(& $pdf, $text, $x, $y, $optlist = [])
	{
	}

################################################################################
# pdf_get_apiname - Get name of unsuccessfull API function
# pdf_get_apiname ( resource $pdf ) : string
# Gets the name of the API function which threw the last exception or failed.
################################################################################

function pdf_get_apiname(& $pdf)
	{
	return($pdf["apiname"]);
	}

################################################################################
# pdf_get_buffer - Get PDF output buffer
# pdf_get_buffer ( resource $pdf ) : string
# Fetches the buffer containing the generated PDF data.
################################################################################

function pdf_get_buffer(& $pdf)
	{
	return($pdf["stream"]);
	}

################################################################################
# pdf_get_errmsg - Get error text
# pdf_get_errmsg ( resource $pdf ) : string
# Gets the text of the last thrown exception or the reason for a failed function call.
################################################################################

function pdf_get_errmsg(& $pdf)
	{
	}

################################################################################
# pdf_get_errnum - Get error number
# pdf_get_errnum ( resource $pdf ) : int
# Gets the number of the last thrown exception or the reason for a failed function call.
################################################################################

function pdf_get_errnum(& $pdf)
	{
	}

################################################################################
# pdf_get_font - Get font [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_get_value() with the parameter font instead.
################################################################################

function pdf_get_font(& $pdf)
	{
	return(pdf_get_value($pdf, "font", 0));
	}

################################################################################
# pdf_get_fontname - Get font name [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_get_parameter() with the parameter fontname instead.
################################################################################

function pdf_get_fontname(& $pdf, $font)
	{
	return(pdf_get_value($pdf, "fontname", $font));
	}

################################################################################
# pdf_get_fontsize - Font handling [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_get_value() with the parameter fontsize instead.
################################################################################

function pdf_get_fontsize(& $pdf)
	{
	return(pdf_get_value($pdf, "fontsize", 0));
	}

################################################################################
# pdf_get_image_height - Get image height [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_get_value() with the parameter imageheight instead.
################################################################################

function pdf_get_image_height(& $pdf, $image)
	{
	return(pdf_get_value($pdf, "imageheight", $image));
	}

################################################################################
# pdf_get_image_width - Get image width [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_get_value() with the parameter imagewidth instead.
################################################################################

function pdf_get_image_width(& $pdf, $image)
	{
	return(pdf_get_value($pdf, "imagewidth", $image));
	}

################################################################################
# pdf_get_majorversion - Get major version number [deprecated]
# pdf_get_majorversion ( void ) : int
# This function is deprecated since PDFlib version 5, use PDF_get_value() with the parameter major instead.
################################################################################

function pdf_get_majorversion()
	{
	$pdf = pdf_new();

	return(pdf_get_value($pdf, "major", 0));
	}

################################################################################
# pdf_get_minorversion - Get minor version number [deprecated]
# pdf_get_minorversion ( void ) : int
# Returns the minor version number of the PDFlib version.
# This function is deprecated since PDFlib version 5, use PDF_get_value() with the parameter minor instead.
################################################################################

function pdf_get_minorversion()
	{
	$pdf = pdf_new();

	return(pdf_get_value($pdf, "minor", 0));
	}

################################################################################
# pdf_get_parameter - Get string parameter
# pdf_get_parameter ( resource $pdf , string $key , float $modifier ) : string
# Gets the contents of some PDFlib parameter with string type.
################################################################################

function pdf_get_parameter(& $pdf, $key, $modifier)
	{
	switch($key)
		{
		case("fontname"):
			# check if font-alias is valid
			if(sscanf($modifier, "/F%d", $whatever) != 1)
				die(__FUNCTION__ . ": invalid font: " . $modifier);

			# check if font-alias is loaded
			if(! isset($pdf["resources"]["/Font"][$modifier]))
				die(__FUNCTION__ . ": font not found: " . $modifier);

			# check if font-resource is valid
			if(sscanf($pdf["resources"]["/Font"][$modifier], "%d %d R", $object_id, $object_version) != 2)
				die(__FUNCTION__ . ": invalid font: " . $modifier);

			# check if font-resource is valid
			if(! isset($pdf["objects"][$object_id]["dictionary"]["/BaseFont"]))
				die(__FUNCTION__ . ": invalid font: " . $modifier);

			# check if font-name is valid
			if(sscanf($pdf["objects"][$object_id]["dictionary"]["/BaseFont"], "/%s", $whatever) != 1)
				die(__FUNCTION__ . ": invalid font: " . $modifier);

			return($whatever);
		default:
			die(__FUNCTION__ . ": invalid key: " . $key);
		}
	}

################################################################################
# pdf_get_pdi_parameter - Get PDI string parameter [deprecated]
# pdf_get_pdi_parameter ( resource $pdf , string $key , int $doc , int $page , int $reserved ) : string
# Gets the contents of a PDI document parameter with string type.
# This function is deprecated since PDFlib version 7, use PDF_pcos_get_string() instead.
################################################################################

function pdf_get_pdi_parameter(& $pdf, $key, $doc, $page, $reserved)
	{
	}

################################################################################
# pdf_get_pdi_value - Get PDI numerical parameter [deprecated]
# pdf_get_pdi_value ( resource $pdf , string $key , int $doc , int $page , int $reserved ) : float
# Gets the contents of a PDI document parameter with numerical type.
# This function is deprecated since PDFlib version 7, use PDF_pcos_get_number() instead.
################################################################################

function pdf_get_pdi_value(& $pdf, $key, $doc, $page, $reserved)
	{
	}

################################################################################
# pdf_get_value - Get numerical parameter
# pdf_get_value ( resource $pdf , string $key , float $modifier ) : float
# Gets the value of some PDFlib parameter with numerical type.
################################################################################

function pdf_get_value(& $pdf, $key, $modifier)
	{
	switch($key)
		{
		case("font"):
			# check if font is set
			if(! isset($pdf["font"]))
				die(__FUNCTION__ . ": font not set.");

			# check if font-alias is valid
			if(sscanf($pdf["font"], "/F%d", $whatever) != 1)
				die(__FUNCTION__ . ": invalid font.");

			# /Fx instead of x
			return($pdf["font"]);
		case("fontsize"):
			# check if fontsize is set
			if(! isset($pdf["fontsize"]))
				die(__FUNCTION__ . ": fontsize not set.");

			# check if fontsize is valid
			if(! is_numeric($pdf["fontsize"]))
				die(__FUNCTION__ . ": invalid fontsize.");

			return($pdf["fontsize"]);
		case("imageheight"):
			# check if image-alias is valid
			if(sscanf($modifier, "/X%d", $whatever) != 1)
				die(__FUNCTION__ . ": invalid image: " . $modifier);

			# check if image-alias is loaded
			if(! isset($pdf["resources"]["/XObject"][$modifier]))
				die(__FUNCTION__ . ": image not found: " . $modifier);

			# check if image-resource is valid
			if(sscanf($pdf["resources"]["/XObject"][$modifier], "%d %d R", $object_id, $object_version) != 2)
				die(__FUNCTION__ . ": invalid pointer: " . $modifier);

			# check if image-resource is valid
			if(! isset($pdf["objects"][$object_id]["dictionary"]["/Height"]))
				die(__FUNCTION__ . ": invalid image: " . $modifier);

			# check if imageheight is valid
			if(! is_numeric($pdf["objects"][$object_id]["dictionary"]["/Height"]))
				die(__FUNCTION__ . ": invalid image: " . $modifier);

			return($pdf["objects"][$object_id]["dictionary"]["/Height"]);
		case("imagewidth"):
			# check if image-alias is valid
			if(sscanf($modifier, "/X%d", $whatever) != 1)
				die(__FUNCTION__ . ": invalid image: " . $modifier);

			# check if image is loaded
			if(! isset($pdf["resources"]["/XObject"][$modifier]))
				die(__FUNCTION__ . ": image not found: " . $modifier);

			# check if image-resource is valid
			if(sscanf($pdf["resources"]["/XObject"][$modifier], "%d %d R", $object_id, $object_version) != 2)
				die(__FUNCTION__ . ": invalid pointer: " . $modifier);

			# check if image-resource is valid
			if(! isset($pdf["objects"][$object_id]["dictionary"]["/Width"]))
				die(__FUNCTION__ . ": invalid image: " . $modifier);

			# check if imagewidth is valid
			if(! is_numeric($pdf["objects"][$object_id]["dictionary"]["/Width"]))
				die(__FUNCTION__ . ": invalid value: " . $modifier);

			return($pdf["objects"][$object_id]["dictionary"]["/Width"]);
		case("major"):
			# check if major-version is set
			if(! isset($pdf["major"]))
				die(__FUNCTION__ . ": version not found.");

			# check if major-version is valid
			if(! is_numeric($pdf["major"]))
				die(__FUNCTION__ . ": invalid version.");

			return($pdf["major"]);
		case("minor"):
			# check if minor-version is set
			if(! isset($pdf["minor"]))
				die(__FUNCTION__ . ": version not found.");

			# check if minor-version is valid
			if(! is_numeric($pdf["minor"]))
				die(__FUNCTION__ . ": invalid version.");

			return($pdf["minor"]);
		default:
			die(__FUNCTION__ . ": invalid key: " . $key);
		}
	}

################################################################################
# pdf_info_font - Query detailed information about a loaded font
# pdf_info_font ( resource $pdf , int $font , string $keyword , string $optlist ) : float
# Queries detailed information about a loaded font.
################################################################################

function pdf_info_font(& $pdf, $font, $keyword, $optlist = [])
	{
	# workaround to allow fontname (Helvetica) instead of its alias (/Fx)
	if(sscanf($font, "/F%d", $whatever) != 1)
		$font = pdf_findfont($pdf, $font, "winansi", 1);

	# one step above the alias was checked
	if(sscanf($font, "/F%d", $iwhatever) != 1)
		die(__FUNCTION__ . ": invalid font.");

	# check if font is loaded
	if(! isset($pdf["resources"]["/Font"][$font]))
		die(__FUNCTION__ . ": font not found.");

	# check if pointer of loaded font is valid
	if(sscanf($pdf["resources"]["/Font"][$font], "%d %d R", $object_id, $object_version) != 2)
		die(__FUNCTION__ . ": invalid pointer.");

	# check if font-resource is valid
	if(! isset($pdf["objects"][$object_id]["dictionary"]))
		die(__FUNCTION__ . ": invalid font: " . $font);

	# check if key is valid
	if(! is_numeric($pdf["objects"][$object_id]["dictionary"][$keyword]))
		die(__FUNCTION__ . ": invalid keyword: " . $keyword);

	return($pdf["objects"][$object_id]["dictionary"][$keyword]);
	}

################################################################################
# pdf_info_matchbox - Query matchbox information
# pdf_info_matchbox ( resource $pdf , string $boxname , int $num , string $keyword ) : float
# Queries information about a matchbox on the current page.
################################################################################

function pdf_info_matchbox(& $pdf, $boxname, $num, $keyword)
	{
	}

################################################################################
# pdf_info_table - Retrieve table information
# pdf_info_table ( resource $pdf , int $table , string $keyword ) : float
# Retrieves table information related to the most recently placed table instance.
################################################################################

function pdf_info_table(& $pdf, $table, $keyword)
	{
	}

################################################################################
# pdf_info_textflow - Query textflow state
# pdf_info_textflow ( resource $pdf , int $textflow , string $keyword ) : float
# Queries the current state of a textflow.
################################################################################

function pdf_info_textflow(& $pdf, $textflow, $keyword)
	{
	}

################################################################################
# pdf_info_textline - Perform textline formatting and query metrics
# pdf_info_textline ( resource $pdf , string $text , string $keyword , string $optlist ) : float
# Performs textline formatting and queries the resulting metrics.
################################################################################

function pdf_info_textline(& $pdf, $text, $keyword, $optlist = [])
	{
	}

################################################################################
# pdf_initgraphics - Reset graphic state
# pdf_initgraphics ( resource $pdf ) : bool
# Reset all color and graphics state parameters to their defaults.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_initgraphics(& $pdf)
	{
	}

################################################################################
# pdf_lineto - Draw a line
# pdf_lineto ( resource $pdf , float $x , float $y ) : bool
# Draws a line from the current point to another point.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_lineto(& $pdf, $x, $y)
	{
	$pdf["stream"][] = sprintf("%f %f l", $x, $y);
	}

################################################################################
# pdf_load_3ddata - Load 3D model
# pdf_load_3ddata ( resource $pdf , string $filename , string $optlist ) : int
# Loads a 3D model from a disk-based or virtual file.
# This function requires PDF 1.6.
################################################################################

function pdf_load_3ddata(& $pdf, $filename, $optlist = [])
	{
	}

################################################################################
# pdf_load_font - Search and prepare font
# pdf_load_font ( resource $pdf , string $fontname , string $encoding , string $optlist ) : int
# Searches for a font and prepares it for later use.
################################################################################

function pdf_load_font(& $pdf, $fontname, $encoding = "builtin", $optlist = "")
	{
#	printf(__FUNCTION__ . ": %s\n" , $fontname);

	# check encoding
	if(! in_array($encoding, ["builtin", "winansi", "macroman", "macexpert"]))
		die(__FUNCTION__ . ": invalid encoding: " . $encoding);

	# check built-in fonts
	foreach($pdf["core"] as $object)
		{
		if($object["name"] != $fontname)
			continue;

		# create new object id
		$font_id = _pdf_get_free_object_id($pdf);

		# create new object (font)
		$pdf["objects"][$font_id] = [
			"id" => $font_id,
			"version" => 0,
			"dictionary" => [
				"/Type" => "/Font",
				"/Subtype" => "/Type1",
				"/BaseFont" => "/" . $fontname
				]
			];

		# valid encodings
		$encodings = ["winansi" => "/WinAnsiEncoding", "macroman" => "/MacRomanEncoding", "macexpert" => "/MacExpertEncoding"];

		# apply encoding
		if($encoding != "builtin") # /StandardEncoding
			if(isset($encodings[$encoding]))
				$pdf["objects"][$font_id]["dictionary"]["/Encoding"] = $encodings[$encoding];
			else
				$pdf["objects"][$font_id]["dictionary"]["/Encoding"] = $encoding;

		
		# apply widths ... this need to be written here, for easier access to object where we get data from
		foreach(["/FirstChar" => 0x00, "/LastChar" => 0xFF, "/Widths" => $object["widths"]] as $key => $value)
			$pdf["objects"][$font_id]["dictionary"][$key] = $value;

		# create new font id, no object id
		$index = _pdf_get_free_font_id($pdf);

		# apply font to list of global resources
		$pdf["resources"]["/Font"][$index] = sprintf("%d %d R", $font_id, 0);

		# return created font id
		return($index);
		}

	# create filename
	$filename = __DIR__ . "/" . $fontname . ".ttf";

	# check font-file
	if(! file_exists($filename))
		return(pdf_load_font($pdf, "Courier", $encoding, $optlist));

	# apply file with additional /Length1
	if($optlist)
		$file = _pdf_add_stream($pdf, file_get_contents($filename), ["/Length1" => filesize($filename)]);
	else
		$file = "";

	# create font-name
	$fontname = basename($filename, ".ttf");

	# apply font-descriptor
	$descriptor = _pdf_add_font_descriptor($pdf, $fontname, $file);

	# create new object id
	$font_id = _pdf_get_free_object_id($pdf);
	$font_version = 0;;

	# create new object (font)
	$pdf["objects"][$font_id] = [
		"id" => $font_id,
		"version" => $font_version,
		"dictionary" => [
			"/Type" => "/Font",
			"/Subtype" => "/TrueType",
			"/BaseFont" => "/" . $fontname,
			"/FirstChar" => 32,
			"/LastChar" => 255,
			"/Widths" => "[]",
			"/FontDescriptor" => $descriptor
			]
		];

	# valid encodings
	$encodings = ["winansi" => "/WinAnsiEncoding", "macroman" => "/MacRomanEncoding", "macexpert" => "/MacExpertEncoding"];

	# apply encoding
	if($encoding != "builtin") # /StandardEncoding
		if(isset($encodings[$encoding]))
			$pdf["objects"][$font_id]["dictionary"]["/Encoding"] = $encodings[$encoding];
		else
			$pdf["objects"][$font_id]["dictionary"]["/Encoding"] = $encoding;

	# apply widths
	$widths = [];

	foreach(range(0x00, 0xFF) as $char)
		$widths[$char] = (($info = imagettfbbox(720, 0, $filename, chr($char))) ? $info[2] : 1000);

	# apply widths
	foreach(["/FirstChar" => 0x00, "/LastChar" => 0xFF, "/Widths" => $widths] as $key => $value)
		$pdf["objects"][$font_id]["dictionary"][$key] = $value;

	# create new font id, no object id
	$index = _pdf_get_free_font_id($pdf);

	# apply font to list of global resources
	$pdf["resources"]["/Font"][$index] = sprintf("%d %d R", $font_id, $font_version);

	# return created font id
	return($index);
	}

################################################################################
# pdf_load_iccprofile - Search and prepare ICC profile
# pdf_load_iccprofile ( resource $pdf , string $profilename , string $optlist ) : int
# Searches for an ICC profile, and prepares it for later use.
################################################################################

function pdf_load_iccprofile(& $pdf, $profilename, $optlist = [])
	{
	}

################################################################################
# pdf_load_image - Open image file
# pdf_load_image ( resource $pdf , string $imagetype , string $filename , string $optlist ) : int
# Opens a disk-based or virtual image file subject to various options.
################################################################################

function pdf_load_image(& $pdf, $imagetype, $filename, $optlist = [])
	{
	if(! file_exists($filename))
		die(__FUNCTION__ . ": file not found: " . $filename);
	elseif($imagetype == "gif")
		{
		if(! function_exists("imagecreatefromgif"))
			die(__FUNCTION__ . ": no gif support.");

		# load image from file
		$handle = imagecreatefromgif($filename);

		if(! $handle)
			die(__FUNCTION__ . ": invalid file: " . $filename);

		imageinterlace($handle, 0);

		if(! function_exists("imagepng"))
			die(__FUNCTION__ . ": no png support.");

		# create temporary name
		$tempnam = tempnam(__DIR__, "xxx");

		if(! $tempnam)
			die(__FUNCTION__ . ": unable to create a temporary file.");

		if(! imagepng($handle, $tempnam))
			die(__FUNCTION__ . ": error while saving to temporary file.");

		# destroy image handle
		imagedestroy($handle);

		# inherit call
		$index = pdf_load_image($pdf, "png", $tempnam);

		# delete temporary file
		unlink($tempnam);

		# return created object id
		return($index);
		}
	elseif($imagetype == "jpeg")
		return(pdf_load_image($pdf, "jpg", $filename));
	elseif($imagetype == "jpg")
		{
		$info = getimagesize($filename);

		if(! $info)
			die(__FUNCTION__ . ": invalid file: " . $filename);

		if($info[2] != 2)
			die(__FUNCTION__ . ": invalid file: " . $filename);

		if(! isset($info["channels"]))
			$color_space = "/DeviceRGB";
		elseif($info["channels"] == 3)
			$color_space = "/DeviceRGB";
		elseif($info["channels"] == 4)
			$color_space = "/DeviceCMYK";
		else
			$color_space = "/DeviceGray";

		################################################################################

		# create new object id
		$image_id = _pdf_get_free_object_id($pdf);
		$image_version = 0;

		# create new object (xobject)
		$pdf["objects"][$image_id] = [
			"id" => $image_id,
			"version" => $image_version,
			"dictionary" => [
				"/Type" => "/XObject",
				"/Subtype" => "/Image",

				# PDF32000-1:2008 8.9.3 Sample Representation
				# The source format for an image shall be described by four parameters:
				"/Width" => $info[0],
				"/Height" => $info[1],
				"/ColorSpace" => $color_space,
				"/BitsPerComponent" => (isset($info["bits"]) ? $info["bits"] : 8), # /DeviceRGB as default ?

				"/Filter" => "/DCTDecode",
				"/Length" => filesize($filename)
				],
			"stream" => file_get_contents($filename)
			];

		# return created object id
		$image = sprintf("%d %d R", $image_id, $image_version);
		}
	elseif($imagetype == "png")
		{
		$handle = fopen($filename, "rb");

		if(! $handle)
			die(__FUNCTION__ . ": invalid file: " . $filename);

		if(_pdf_read_str($handle, 8) != "\x89PNG\x0D\x0A\x1A\x0A")
			die(__FUNCTION__ . ": invalid file: " . $filename);

		$trns_stream = [];
		$plte_stream = "";
		$data_stream = "";

		# hexdec(bin2hex($a))

		do
			{
			$chunk_length = _pdf_read_lng($handle);
			$chunk_type = _pdf_read_str($handle, 4);
			$chunk_data = "";

			if($chunk_type == "PLTE")
				$plte_stream .= _pdf_read_str($handle, $chunk_length);
			elseif($chunk_type == "IDAT")
				$data_stream .= _pdf_read_str($handle, $chunk_length);
			elseif($chunk_type == "IHDR")
				{
				$width = _pdf_read_lng($handle);
				$height = _pdf_read_lng($handle);
				$bits_per_component = _pdf_read_chr($handle);
				$color_type = _pdf_read_chr($handle);
				$compression_method = _pdf_read_chr($handle);
				$filter_method = _pdf_read_chr($handle);
				$interlacing = _pdf_read_chr($handle);

				if($bits_per_component > 0x08)
					die(__FUNCTION__ . ": 16-bit depth not supported: " . $filename);

				if($compression_method != 0x00)
					die(__FUNCTION__ . ": unknown compression method: " . $filename);

				if($filter_method != 0x00)
					die(__FUNCTION__ . ": unknown filter method: " . $filename);

				if($interlacing != 0x00)
					die(__FUNCTION__ . ": interlacing not supported: " . $filename);
				}
			elseif($chunk_type == "IEND")
				break;
			elseif($chunk_type == "bKGD")
				$chunk_data = _pdf_read_str($handle, $chunk_length);
			elseif($chunk_type == "pHYs")
				$chunk_data = _pdf_read_str($handle, $chunk_length);
			elseif($chunk_type == "tEXt")
				$chunk_data = _pdf_read_str($handle, $chunk_length);
			elseif($chunk_type == "tIMe")
				$chunk_data = _pdf_read_str($handle, $chunk_length);
			elseif($chunk_type == "tRNS")
				{
				$chunk_data = _pdf_read_str($handle, $chunk_length);

				if($color_type & 0x02)
					$trns_stream[] = [ord($chunk_data[0])];
				else
					$trns_stream[] = [ord($chunk_data[1]), ord($chunk_data[3]), ord($chunk_data[5])]; # 135 or 012
				}
			else
				$chunk_data = _pdf_read_str($handle, $chunk_length);

			$chunk_crc = _pdf_read_lng($handle);
			}
		while($chunk_length);

		fclose($handle);

		################################################################################
		# palette stream

		if($color_type & 0x01)
			$p = _pdf_add_stream($pdf, $plte_stream);

		################################################################################
		# alpha channel

		if($color_type & 0x04)
			{
			$data_stream = gzuncompress($data_stream);

			$color_stream = "";
			$alpha_stream = "";

			$colors = ($color_type & 0x02 ? 3 : 1);

			for($m = 0; $m < $height; $m ++)
				{
				$x = $width * ($colors + 1);
				$y = $m * ($x + 1);
				$z = substr($data_stream, $y + 1, $x);

				$alpha_stream .= $data_stream[$y] . preg_replace("/.{" . $colors . "}(.)/s", "$1", $z);
				$color_stream .= $data_stream[$y] . preg_replace("/(.{" . $colors . "})./s", "$1", $z);
				}

			$alpha_stream = gzcompress($alpha_stream, 9); # full power
			$data_stream = gzcompress($color_stream, 9); # full power

			################################################################################
			# apply alpha stream

			# create new object id
			$smask_id = _pdf_get_free_object_id($pdf);
			$smask_version = 0;

			# create new object (image)
			$pdf["objects"][$smask_id] = [
				"id" => $smask_id,
				"version" => $smask_version,
				"dictionary" => [
					# optional
					"/Type" => "/Object",
					"/Subtype" => "/Image",

					# PDF32000-1:2008 8.9.3 Sample Representation
					# The source format for an image shall be described by four parameters:
					"/Width" => $width,
					"/Height" => $height,
					"/ColorSpace" => "/DeviceGray", # ???
					"/BitsPerComponent" => $bits_per_component,

					"/DecodeParms" => [
						"/Predictor" => 15,
						"/Colors" => 0, # ???
						"/BitsPerComponent" => $bits_per_component,
						"/Columns" => $width
						],

					"/Filter" => "/FlateDecode",
					"/Length" => strlen($alpha_stream)
					],
				"stream" => $alpha_stream
				];

			# return created object id
			$s = sprintf("%d %d R", $smask_id, $smask_version);
			}

		################################################################################
		# apply image data

		# create new object id
		$image_id = _pdf_get_free_object_id($pdf);
		$image_version = 0;

		# create new object (image)
		$pdf["objects"][$image_id] = [
			"id" => $image_id,
			"version" => $image_version,
			"dictionary" => [
				"/Type" => "/XObject",
				"/Subtype" => "/Image",

				# PDF32000-1:2008 8.9.3 Sample Representation
				# The source format for an image shall be described by four parameters:
				"/Width" => $width,
				"/Height" => $height,
				"/ColorSpace" => ($color_type & 0x02 ? "/DeviceRGB" : "/DeviceGray"),
				"/BitsPerComponent" => $bits_per_component,

				"/DecodeParms" => [
					"/Predictor" => 15,
					"/Colors" => ($color_type & 0x01 ? 1 : ($color_type & 0x02 ? 3 : 1)), # BW as default?
					"/BitsPerComponent" => $bits_per_component,
					"/Columns" => $width
					],

				"/Filter" => "/FlateDecode",
				"/Length" => strlen($data_stream)
				],
			"stream" => $data_stream
			];

		# palette stream
		if($color_type & 0x01)
			$pdf["objects"][$image_id]["dictionary"]["/ColorSpace"] = sprintf("[%s %s %d %s]", "/Indexed", "/DeviceRGB", strlen($plte_stream) / 3 - 1, $p);

		# alpha channel
		if($color_type & 0x04)
			$pdf["objects"][$image_id]["dictionary"]["/SMask"] = $s;

		# transparency
		if($trns_stream)
			{
			$mask = [];

			foreach($trns_stream as $t)
				$mask[] = implode(" ", [$t[0], $t[0]]); # this is 1? check for 3!

			$pdf["objects"][$image_id]["dictionary"]["/Mask"] = sprintf("[%s]", _pdf_glue_array($mask));
			}

		# return created object id
		$image = sprintf("%d %d R", $image_id, $image_version);
		}
	else
		{
		# create temp file
		$tempnam = tempnam(__DIR__, "xxx");

		# check temp file
		if(! $tempnam)
			die(__FUNCTION__ . ": unable to create a temporary file.");

		# convert file by using external component
		exec("convert \"" . $filename . "\" -quality 50 \"" . $tempnam . ".jpg\"");

		# load image
		$index = pdf_load_image($pdf, "jpg", $tempnam . ".jpg");

		# unlink temp file
		unlink($tempnam . ".jpg");
		unlink($tempnam);

		# return created object id
		return($index);
		}

	# create new xobject id, no object id
	$index = _pdf_get_free_xobject_id($pdf);

	# apply image id to list of global resources
	$pdf["resources"]["/XObject"][$index] = $image;

	# return created xobject id
	return($index);
	}

################################################################################
# pdf_makespotcolor - Make spot color
# pdf_makespotcolor ( resource $pdf , string $spotname ) : int
# Finds a built-in spot color name, or makes a named spot color from the current fill color.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_makespotcolor(& $pdf, $spotname)
	{
	}

################################################################################
# pdf_moveto - Set current point
# pdf_moveto ( resource $pdf , float $x , float $y ) : bool
# Sets the current point for graphics output.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_moveto(& $pdf, $x, $y)
	{
	$pdf["stream"][] = sprintf("%f %f m", $x, $y);
	}

################################################################################
# pdf_new - Create PDFlib object
# pdf_new ( void ) : resource
# Creates a new PDFlib object with default settings.
################################################################################

function pdf_new()
	{
	# define standard font (compression can save 10 kb)
	$core = [
			[
			"name" => "Courier",
			"widths" => array_fill(0, 256, 707)
			],
			[
			"name" => "Courier-Bold",
			"widths" => array_fill(0, 256, 707)
			],
			[
			"name" => "Courier-BoldOblique",
			"widths" => array_fill(0, 256, 707)
			],
			[
			"name" => "Courier-Oblique",
			"widths" => array_fill(0, 256, 707)
			],
			[
			"name" => "Helvetica",
			"widths" => [
				0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
				556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
				1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
				667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
				333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
				556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584, 350,
				556, 350, 222, 556, 333, 1000, 556, 556, 333, 1000, 667, 333, 1000, 350, 611, 350,
				350, 222, 222, 333, 333, 350, 556, 1000, 333, 1000, 500, 333, 944, 350, 500, 667,
				278, 333, 556, 556, 556, 556, 260, 556, 333, 737, 370, 556, 584, 333, 737, 333,
				400, 584, 333, 333, 333, 556, 537, 278, 333, 333, 365, 556, 834, 834, 834, 611,
				667, 667, 667, 667, 667, 667, 1000, 722, 667, 667, 667, 667, 278, 278, 278, 278,
				722, 722, 778, 778, 778, 778, 778, 584, 778, 722, 722, 722, 722, 667, 667, 611,
				556, 556, 556, 556, 556, 556, 889, 500, 556, 556, 556, 556, 278, 278, 278, 278,
				556, 556, 556, 556, 556, 556, 556, 584, 611, 556, 556, 556, 556, 500, 556, 500
				]
			],
			[
			"name" => "Helvetica-Bold",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 278, 333, 474, 556, 556, 889, 722, 238, 333, 333, 389, 584, 278, 333, 278, 278,
				 556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 333, 333, 584, 584, 584, 611,
				 975, 722, 722, 722, 722, 667, 611, 778, 722, 278, 556, 722, 611, 833, 722, 778,
				 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 333, 278, 333, 584, 556,
				 333, 556, 611, 556, 611, 556, 333, 611, 611, 278, 278, 556, 278, 889, 611, 611,
				 611, 611, 389, 556, 333, 611, 556, 778, 556, 556, 500, 389, 280, 389, 584, 350,
				 556, 350, 278, 556, 500, 1000, 556, 556, 333, 1000, 667, 333, 1000, 350, 611, 350,
				 350, 278, 278, 500, 500, 350, 556, 1000, 333, 1000, 556, 333, 944, 350, 500, 667,
				 278, 333, 556, 556, 556, 556, 280, 556, 333, 737, 370, 556, 584, 333, 737, 333,
				 400, 584, 333, 333, 333, 611, 556, 278, 333, 333, 365, 556, 834, 834, 834, 611,
				 722, 722, 722, 722, 722, 722, 1000, 722, 667, 667, 667, 667, 278, 278, 278, 278,
				 722, 722, 778, 778, 778, 778, 778, 584, 778, 722, 722, 722, 722, 667, 667, 611,
				 556, 556, 556, 556, 556, 556, 889, 556, 556, 556, 556, 556, 278, 278, 278, 278,
				 611, 611, 611, 611, 611, 611, 611, 584, 611, 611, 611, 611, 611, 556, 611, 556
				]
			],
			[
			"name" => "Helvetica-BoldOblique",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 278, 333, 474, 556, 556, 889, 722, 238, 333, 333, 389, 584, 278, 333, 278, 278,
				 556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 333, 333, 584, 584, 584, 611,
				 975, 722, 722, 722, 722, 667, 611, 778, 722, 278, 556, 722, 611, 833, 722, 778,
				 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 333, 278, 333, 584, 556,
				 333, 556, 611, 556, 611, 556, 333, 611, 611, 278, 278, 556, 278, 889, 611, 611,
				 611, 611, 389, 556, 333, 611, 556, 778, 556, 556, 500, 389, 280, 389, 584, 350,
				 556, 350, 278, 556, 500, 1000, 556, 556, 333, 1000, 667, 333, 1000, 350, 611, 350,
				 350, 278, 278, 500, 500, 350, 556, 1000, 333, 1000, 556, 333, 944, 350, 500, 667,
				 278, 333, 556, 556, 556, 556, 280, 556, 333, 737, 370, 556, 584, 333, 737, 333,
				 400, 584, 333, 333, 333, 611, 556, 278, 333, 333, 365, 556, 834, 834, 834, 611,
				 722, 722, 722, 722, 722, 722, 1000, 722, 667, 667, 667, 667, 278, 278, 278, 278,
				 722, 722, 778, 778, 778, 778, 778, 584, 778, 722, 722, 722, 722, 667, 667, 611,
				 556, 556, 556, 556, 556, 556, 889, 556, 556, 556, 556, 556, 278, 278, 278, 278,
				 611, 611, 611, 611, 611, 611, 611, 584, 611, 611, 611, 611, 611, 556, 611, 556
				]
			],
			[
			"name" => "Helvetica-Oblique",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 278, 278, 355, 556, 556, 889, 667, 191, 333, 333, 389, 584, 278, 333, 278, 278,
				 556, 556, 556, 556, 556, 556, 556, 556, 556, 556, 278, 278, 584, 584, 584, 556,
				 1015, 667, 667, 722, 722, 667, 611, 778, 722, 278, 500, 667, 556, 833, 722, 778,
				 667, 778, 722, 667, 611, 722, 667, 944, 667, 667, 611, 278, 278, 278, 469, 556,
				 333, 556, 556, 500, 556, 556, 278, 556, 556, 222, 222, 500, 222, 833, 556, 556,
				 556, 556, 333, 500, 278, 556, 500, 722, 500, 500, 500, 334, 260, 334, 584, 350,
				 556, 350, 222, 556, 333, 1000, 556, 556, 333, 1000, 667, 333, 1000, 350, 611, 350,
				 350, 222, 222, 333, 333, 350, 556, 1000, 333, 1000, 500, 333, 944, 350, 500, 667,
				 278, 333, 556, 556, 556, 556, 260, 556, 333, 737, 370, 556, 584, 333, 737, 333,
				 400, 584, 333, 333, 333, 556, 537, 278, 333, 333, 365, 556, 834, 834, 834, 611,
				 667, 667, 667, 667, 667, 667, 1000, 722, 667, 667, 667, 667, 278, 278, 278, 278,
				 722, 722, 778, 778, 778, 778, 778, 584, 778, 722, 722, 722, 722, 667, 667, 611,
				 556, 556, 556, 556, 556, 556, 889, 500, 556, 556, 556, 556, 278, 278, 278, 278,
				 556, 556, 556, 556, 556, 556, 556, 584, 611, 556, 556, 556, 556, 500, 556, 500
				]
			],
			[
			"name" => "Symbol",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 250, 333, 713, 500, 549, 833, 778, 439, 333, 333, 500, 549, 250, 549, 250, 278,
				 500, 500, 500, 500, 500, 500, 500, 500, 500, 500, 278, 278, 549, 549, 549, 444,
				 549, 722, 667, 722, 612, 611, 763, 603, 722, 333, 631, 722, 686, 889, 722, 722,
				 768, 741, 556, 592, 611, 690, 439, 768, 645, 795, 611, 333, 863, 333, 658, 500,
				 500, 631, 549, 549, 494, 439, 521, 411, 603, 329, 603, 549, 549, 576, 521, 549,
				 549, 521, 549, 603, 439, 576, 713, 686, 493, 686, 494, 480, 200, 480, 549, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 750, 620, 247, 549, 167, 713, 500, 753, 753, 753, 753, 1042, 987, 603, 987, 603,
				 400, 549, 411, 549, 549, 713, 494, 460, 549, 549, 549, 549, 1000, 603, 1000, 658,
				 823, 686, 795, 987, 768, 768, 823, 768, 768, 713, 713, 713, 713, 713, 713, 713,
				 768, 713, 790, 790, 890, 823, 549, 250, 713, 603, 603, 1042, 987, 603, 987, 603,
				 494, 329, 790, 790, 786, 713, 384, 384, 384, 384, 384, 384, 494, 494, 494, 494,
				 0, 329, 274, 686, 686, 686, 384, 384, 384, 384, 384, 384, 494, 494, 494, 0
				 ]
			],
			[
			"name" => "Times-Roman",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 250, 333, 408, 500, 500, 833, 778, 180, 333, 333, 500, 564, 250, 333, 250, 278,
				 500, 500, 500, 500, 500, 500, 500, 500, 500, 500, 278, 278, 564, 564, 564, 444,
				 921, 722, 667, 667, 722, 611, 556, 722, 722, 333, 389, 722, 611, 889, 722, 722,
				 556, 722, 667, 556, 611, 722, 722, 944, 722, 722, 611, 333, 278, 333, 469, 500,
				 333, 444, 500, 444, 500, 444, 333, 500, 500, 278, 278, 500, 278, 778, 500, 500,
				 500, 500, 333, 389, 278, 500, 500, 722, 500, 500, 444, 480, 200, 480, 541, 350,
				 500, 350, 333, 500, 444, 1000, 500, 500, 333, 1000, 556, 333, 889, 350, 611, 350,
				 350, 333, 333, 444, 444, 350, 500, 1000, 333, 980, 389, 333, 722, 350, 444, 722,
				 250, 333, 500, 500, 500, 500, 200, 500, 333, 760, 276, 500, 564, 333, 760, 333,
				 400, 564, 300, 300, 333, 500, 453, 250, 333, 300, 310, 500, 750, 750, 750, 444,
				 722, 722, 722, 722, 722, 722, 889, 667, 611, 611, 611, 611, 333, 333, 333, 333,
				 722, 722, 722, 722, 722, 722, 722, 564, 722, 722, 722, 722, 722, 722, 556, 500,
				 444, 444, 444, 444, 444, 444, 667, 444, 444, 444, 444, 444, 278, 278, 278, 278,
				 500, 500, 500, 500, 500, 500, 500, 564, 500, 500, 500, 500, 500, 500, 500, 500
				]
			],
			[
			"name" => "Times-Bold",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 250, 333, 555, 500, 500, 1000, 833, 278, 333, 333, 500, 570, 250, 333, 250, 278,
				 500, 500, 500, 500, 500, 500, 500, 500, 500, 500, 333, 333, 570, 570, 570, 500,
				 930, 722, 667, 722, 722, 667, 611, 778, 778, 389, 500, 778, 667, 944, 722, 778,
				 611, 778, 722, 556, 667, 722, 722, 1000, 722, 722, 667, 333, 278, 333, 581, 500,
				 333, 500, 556, 444, 556, 444, 333, 500, 556, 278, 333, 556, 278, 833, 556, 500,
				 556, 556, 444, 389, 333, 556, 500, 722, 500, 500, 444, 394, 220, 394, 520, 350,
				 500, 350, 333, 500, 500, 1000, 500, 500, 333, 1000, 556, 333, 1000, 350, 667, 350,
				 350, 333, 333, 500, 500, 350, 500, 1000, 333, 1000, 389, 333, 722, 350, 444, 722,
				 250, 333, 500, 500, 500, 500, 220, 500, 333, 747, 300, 500, 570, 333, 747, 333,
				 400, 570, 300, 300, 333, 556, 540, 250, 333, 300, 330, 500, 750, 750, 750, 500,
				 722, 722, 722, 722, 722, 722, 1000, 722, 667, 667, 667, 667, 389, 389, 389, 389,
				 722, 722, 778, 778, 778, 778, 778, 570, 778, 722, 722, 722, 722, 722, 611, 556,
				 500, 500, 500, 500, 500, 500, 722, 444, 444, 444, 444, 444, 278, 278, 278, 278,
				 500, 556, 500, 500, 500, 500, 500, 570, 500, 556, 556, 556, 556, 500, 556, 500
				]
			],
			[
			"name" => "Times-BoldOblique",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 250, 389, 555, 500, 500, 833, 778, 278, 333, 333, 500, 570, 250, 333, 250, 278,
				 500, 500, 500, 500, 500, 500, 500, 500, 500, 500, 333, 333, 570, 570, 570, 500,
				 832, 667, 667, 667, 722, 667, 667, 722, 778, 389, 500, 667, 611, 889, 722, 722,
				 611, 722, 667, 556, 611, 722, 667, 889, 667, 611, 611, 333, 278, 333, 570, 500,
				 333, 500, 500, 444, 500, 444, 333, 500, 556, 278, 278, 500, 278, 778, 556, 500,
				 500, 500, 389, 389, 278, 556, 444, 667, 500, 444, 389, 348, 220, 348, 570, 350,
				 500, 350, 333, 500, 500, 1000, 500, 500, 333, 1000, 556, 333, 944, 350, 611, 350,
				 350, 333, 333, 500, 500, 350, 500, 1000, 333, 1000, 389, 333, 722, 350, 389, 611,
				 250, 389, 500, 500, 500, 500, 220, 500, 333, 747, 266, 500, 606, 333, 747, 333,
				 400, 570, 300, 300, 333, 576, 500, 250, 333, 300, 300, 500, 750, 750, 750, 500,
				 667, 667, 667, 667, 667, 667, 944, 667, 667, 667, 667, 667, 389, 389, 389, 389,
				 722, 722, 722, 722, 722, 722, 722, 570, 722, 722, 722, 722, 722, 611, 611, 500,
				 500, 500, 500, 500, 500, 500, 722, 444, 444, 444, 444, 444, 278, 278, 278, 278,
				 500, 556, 500, 500, 500, 500, 500, 570, 500, 556, 556, 556, 556, 444, 500, 444
				]
			],
			[
			"name" => "Times-Oblique",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 250, 333, 420, 500, 500, 833, 778, 214, 333, 333, 500, 675, 250, 333, 250, 278,
				 500, 500, 500, 500, 500, 500, 500, 500, 500, 500, 333, 333, 675, 675, 675, 500,
				 920, 611, 611, 667, 722, 611, 611, 722, 722, 333, 444, 667, 556, 833, 667, 722,
				 611, 722, 611, 500, 556, 722, 611, 833, 611, 556, 556, 389, 278, 389, 422, 500,
				 333, 500, 500, 444, 500, 444, 278, 500, 500, 278, 278, 444, 278, 722, 500, 500,
				 500, 500, 389, 389, 278, 500, 444, 667, 444, 444, 389, 400, 275, 400, 541, 350,
				 500, 350, 333, 500, 556, 889, 500, 500, 333, 1000, 500, 333, 944, 350, 556, 350,
				 350, 333, 333, 556, 556, 350, 500, 889, 333, 980, 389, 333, 667, 350, 389, 556,
				 250, 389, 500, 500, 500, 500, 275, 500, 333, 760, 276, 500, 675, 333, 760, 333,
				 400, 675, 300, 300, 333, 500, 523, 250, 333, 300, 310, 500, 750, 750, 750, 500,
				 611, 611, 611, 611, 611, 611, 889, 667, 611, 611, 611, 611, 333, 333, 333, 333,
				 722, 667, 722, 722, 722, 722, 722, 675, 722, 722, 722, 722, 722, 556, 611, 500,
				 500, 500, 500, 500, 500, 500, 667, 444, 444, 444, 444, 444, 278, 278, 278, 278,
				 500, 500, 500, 500, 500, 500, 500, 675, 500, 500, 500, 500, 500, 444, 500, 444
				]
			],
			[
			"name" => "ZapfDingbats",
			"widths" => [
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 278, 974, 961, 974, 980, 719, 789, 790, 791, 690, 960, 939, 549, 855, 911, 933,
				 911, 945, 974, 755, 846, 762, 761, 571, 677, 763, 760, 759, 754, 494, 552, 537,
				 577, 692, 786, 788, 788, 790, 793, 794, 816, 823, 789, 841, 823, 833, 816, 831,
				 923, 744, 723, 749, 790, 792, 695, 776, 768, 792, 759, 707, 708, 682, 701, 826,
				 815, 789, 789, 707, 687, 696, 689, 786, 787, 713, 791, 785, 791, 873, 761, 762,
				 762, 759, 759, 892, 892, 788, 784, 438, 138, 277, 415, 392, 392, 668, 668, 0,
				 390, 390, 317, 317, 276, 276, 509, 509, 410, 410, 234, 234, 334, 334, 0, 0,
				 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0,
				 0, 732, 544, 544, 910, 667, 760, 760, 776, 595, 694, 626, 788, 788, 788, 788,
				 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788,
				 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788, 788,
				 788, 788, 788, 788, 894, 838, 1016, 458, 748, 924, 748, 918, 927, 928, 928, 834,
				 873, 828, 924, 924, 917, 930, 931, 463, 883, 836, 836, 867, 867, 696, 696, 874,
				 0, 874, 760, 946, 771, 865, 771, 888, 967, 888, 831, 873, 927, 970, 918, 0
				]
			]
		];

	# finally our "pseudo-class"
	$retval = [
		"active" => "0 0 R",
		"apiname" => sprintf("%s %d.%d.%d (PHP/%s/%s)", "pdf-api", 1, 0, 0, PHP_OS, basename(__FILE__)),
		"core" => $core,
		"filename" => "",
		"font" => "/F0",
		"fontsize" => 0,
		"major" => 1,
		"minor" => 3,
		"objects" => [["id" => 0, "version" => 65535, "dictionary" => ["/Size" => 0]]],
		"resources" => [],
		# font and fontsize should also be part of state
#		"state" => [["charspacing" => 0, "horizscaling" => 0, "leading" => 0, "linecap" => 0, "linejoin" => 0, "linewidth" => 0, "miterlimit" => 0, "textrendering" => 0, "textrise" => 0, "wordspacing" => 0]],
		"stream" => []
		];

	return($retval);
	}

################################################################################
# pdf_open_ccitt - Open raw CCITT image [deprecated]
# pdf_open_ccitt ( resource $pdf , string $filename , int $width , int $height , int $BitReverse , int $k , int $Blackls1 ) : int
# Opens a raw CCITT image.
# This function is deprecated since PDFlib version 5, use PDF_load_image() instead.
################################################################################

function pdf_open_ccitt(& $pdf, $filename, $width, $height, $BitReverse, $k, $Blackls1)
	{
	$optlit = [
		"width" => $width,
		"height" => $height,
		"bitreverse" => $BitReverse,
		"k" => $k,
		"blacklsl" => $Blackls1
		];

	return(pdf_load_image($pdf, "ccitt", $filename, $optlist));
	}

################################################################################
# pdf_open_file - Create PDF file [deprecated]
# pdf_open_file ( resource $pdf , string $filename ) : bool
# Creates a new PDF file using the supplied file name.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use PDF_begin_document() instead.
################################################################################

function pdf_open_file(& $pdf, $filename)
	{
	return(pdf_begin_document($pdf, $filename));
	}

################################################################################
# pdf_open_gif - Open GIF image [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_load_image() instead.
################################################################################

function pdf_open_gif(& $pdf, $filename)
	{
	return(pdf_load_image($pdf, "gif", $filename));
	}

################################################################################
# pdf_open_image_file - Read image from file [deprecated]
# pdf_open_image_file ( resource $pdf , string $imagetype , string $filename , string $stringparam , int $intparam ) : int
# Opens an image file.
# This function is deprecated since PDFlib version 5, use PDF_load_image() with the colorize, ignoremask, invert, mask, masked, and page options instead.
################################################################################

function pdf_open_image_file(& $pdf, $imagetype, $filename, $stringparam = "", $intparam = 0)
	{
	$optlist = [
		"colorize" => 0,
		"ignoremask" => 0,
		"invert" => 0,
		"mask" => 0,
		"masked" => 0,
		"page" => 0
		];

	return(pdf_load_image($pdf, $imagetype, $filename, $optlist));
	}

################################################################################
# pdf_open_image - Use image data [deprecated]
# pdf_open_image ( resource $pdf , string $imagetype , string $source , string $data , int $length , int $width , int $height , int $components , int $bpc , string $params ) : int
# Uses image data from a variety of data sources.
# This function is deprecated since PDFlib version 5, use virtual files and PDF_load_image() instead.
################################################################################

function pdf_open_image(& $pdf, $imagetype, $source, $data, $length, $width, $height, $component, $bpc, $params)
	{
	$optlist = [
		"data" => $data,
		"length" => $length,
		"width" => $width,
		"height" => $height,
		"component" => $component,
		"bits_per_component" => $bpc,
		"params" => $params
		];

	return(pdf_load_image($pdf, $imagetype, "", $optlist));
	}

################################################################################
# pdf_open_jpeg - Open JPEG image [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_load_image() instead.
################################################################################

function pdf_open_jpeg(& $pdf, $filename)
	{
	return(pdf_load_image($pdf, "jpg", $filename));
	}

################################################################################
# pdf_open_memory_image - Open image created with PHP's image functions [not supported]
# pdf_open_memory_image ( resource $pdf , resource $image ) : int
# This function is not supported by PDFlib GmbH.
################################################################################

function pdf_open_memory_image(& $pdf, $image)
	{
	# pdf_open_image uses php's image functions
	# smells like imagecreatefromgif(); used for png manipulation
	# use pdf_load_image
	}

################################################################################
# pdf_open_pdi_document - Prepare a pdi document
# pdf_open_pdi_document ( resource $pdf , string $filename , string $optlist ) : int
# Open a disk-based or virtual PDF document and prepare it for later use.
################################################################################

function pdf_open_pdi_document(& $pdf, $filename, $optlist = [])
	{
	}

################################################################################
# pdf_open_pdi_page - Prepare a page
# pdf_open_pdi_page ( resource $pdf , int $doc , int $pagenumber , string $optlist ) : int
# Prepares a page for later use with PDF_fit_pdi_page().
################################################################################

function pdf_open_pdi_page(& $pdf, $doc, $pagenumber, $optlist = [])
	{
	}

################################################################################
# pdf_open_pdi - Open PDF file [deprecated]
# pdf_open_pdi ( resource $pdf , string $filename , string $optlist , int $len ) : int
# Opens a disk-based or virtual PDF document and prepares it for later use.
# This function is deprecated since PDFlib version 7, use PDF_open_pdi_document() instead.
################################################################################

function pdf_open_pdi(& $pdf, $filename, $optlist = [])
	{
	return(pdf_open_pdi_document($pdf, $filename, $optlist));
	}

################################################################################
# pdf_open_tiff - Open TIFF image [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_load_image() instead.
################################################################################

function pdf_open_tiff(& $pdf, $filename)
	{
	return(pdf_load_image($pdf, "tiff", $filename));
	}

################################################################################
# pdf_pcos_get_number - Get value of pCOS path with type number or boolean
# pdf_pcos_get_number ( resource $pdf , int $doc , string $path ) : float
# Gets the value of a pCOS path with type number or boolean.
################################################################################

function pdf_pcos_get_number(& $pdf, $doc, $path)
	{
	}

################################################################################
# pdf_pcos_get_stream - Get contents of pCOS path with type stream, fstream, or string
# pdf_pcos_get_stream ( resource $pdf , int $doc , string $optlist , string $path ) : string
# Gets the contents of a pCOS path with type stream, fstream, or string.
################################################################################

function pdf_pcos_get_stream(& $pdf, $doc, $optlist, $path)
	{
	}

################################################################################
# pdf_pcos_get_string - Get value of pCOS path with type name, string, or boolean
# pdf_pcos_get_string ( resource $pdf , int $doc , string $path ) : string
# Gets the value of a pCOS path with type name, string, or boolean.
################################################################################

function pdf_pcos_get_string(& $pdf, $doc, $path)
	{
	}

################################################################################
# pdf_place_image - Place image on the page [deprecated]
# pdf_place_image ( resource $pdf , int $image , float $x , float $y , float $scale ) : bool
# Places an image and scales it.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 5, use PDF_fit_image() instead.
################################################################################

function pdf_place_image(& $pdf, $image, $x, $y, $scale)
	{
	pdf_fit_image($pdf, $image, $x, $y, ["scale" => $scale]);
	}

################################################################################
# pdf_place_pdi_page - Place PDF page [deprecated]
# pdf_place_pdi_page ( resource $pdf , int $page , float $x , float $y , float $sx , float $sy ) : bool
# Places a PDF page and scales it.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 5, use PDF_fit_pdi_page() instead.
################################################################################

function pdf_place_pdi_page(& $pdf, $page, $x, $y, $sx, $sy)
	{
	pdf_fit_pdi_page($pdf, $page, $x, $y, ["sx" => $sx, "sy" => $sy]);
	}

################################################################################
# pdf_process_pdi - Process imported PDF document
# pdf_process_pdi ( resource $pdf , int $doc , int $page , string $optlist ) : int
# Processes certain elements of an imported PDF document.
################################################################################

function pdf_process_pdi(& $pdf, $doc, $page, $optlist = [])
	{
	}

################################################################################
# pdf_rect - Draw rectangle
# pdf_rect ( resource $pdf , float $x , float $y , float $width , float $height ) : bool
# Draws a rectangle.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_rect(& $pdf, $x, $y, $width, $height)
	{
	$pdf["stream"][] = sprintf("%f %f %f %f re", $x, $y, $width, $height);
	}

################################################################################
# pdf_restore - Restore graphics state
# pdf_restore ( resource $pdf ) : bool
# Restores the most recently saved graphics state.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_restore(& $pdf)
	{
	$pdf["stream"][] = "Q";
	}

################################################################################
# pdf_resume_page - Resume page
# pdf_resume_page ( resource $pdf , string $optlist ) : bool
# Resumes a page to add more content to it.
################################################################################

function pdf_resume_page(& $pdf, $optlist = [])
	{
	# check stream
	if($pdf["stream"])
		die(__FUNCTION__ . ": page can not be resumed while page is open.");

	# check if active is set
	if(! isset($optlist["active"]))
		die(__FUNCTION__ . ": page must be set as option.");

	# check if pointer to active is valid
	if(sscanf($optlist["active"], "%d %d R", $page_id, $page_version) != 2)
		die(__FUNCTION__ . ": invalid pointer.");

	# check if contents is set
	if(! isset($pdf["objects"][$page_id]["dictionary"]["/Contents"]))
		die(__FUNCTION__ . ": contents not found.");

	# check if pointer to contents is valid
	if(sscanf($pdf["objects"][$page_id]["dictionary"]["/Contents"], "%d %d R", $contents_id, $contents_version) != 2)
		die(__FUNCTION__ . ": invalid pointer.");

	# check if resources is set
	if(! isset($pdf["objects"][$page_id]["dictionary"]["/Resources"]))
		die(__FUNCTION__ . ": resources not found.");

	# check if stream is set
	if(! isset($pdf["objects"][$contents_id]["stream"]))
		die(__FUNCTION__ . ": stream not found.");

	$data = $pdf["objects"][$page_id]["dictionary"]["/Resources"];

#	$data = substr($data, 2);
#	list($resources, $data) = pdf_parse_dictionary();
#	$data = substr($data, 2);

#	$pdf["objects"][$page_id]["dictionary"]["/Resources"] = $resources;

	$pdf["stream"] = $pdf["objects"][$contents_id]["stream"];
	}

################################################################################
# pdf_rotate - Rotate coordinate system
# pdf_rotate ( resource $pdf , float $phi ) : bool
# Rotates the coordinate system.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_rotate(& $pdf, $phi)
	{
	$sin = sin($phi * M_PI / 180);
	$cos = cos($phi * M_PI / 180);

	pdf_concat($pdf, 0 + $cos, 0 + $sin, 0 - $sin, 0 + $cos, 0, 0);
	}

################################################################################
# pdf_save - Save graphics state
# pdf_save ( resource $pdf ) : bool
# Saves the current graphics state.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_save(& $pdf)
	{
	$pdf["stream"][] = "q";
	}

################################################################################
# pdf_scale — Scale coordinate system
# pdf_scale ( resource $pdf , float $sx , float $sy ) : bool
# Scales the coordinate system.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_scale(& $pdf, $sx, $sy)
	{
	pdf_concat($pdf, $sx, 0, 0, $sy, 0, 0);
	}

################################################################################
# pdf_set_border_color — Set border color of annotations [deprecated]
# pdf_set_border_color ( resource $pdf , float $red , float $green , float $blue ) : bool
# Sets the border color for all kinds of annotations.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use the option annotcolor in PDF_create_annotation() instead.
################################################################################

function pdf_set_border_color(& $pdf, $red, $green, $blue)
	{
	}

################################################################################
# pdf_set_border_dash — Set border dash style of annotations [deprecated]
# pdf_set_border_dash ( resource $pdf , float $black , float $white ) : bool
# Sets the border dash style for all kinds of annotations.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use the option dasharray in PDF_create_annotation() instead.
################################################################################

function pdf_set_border_dash(& $pdf, $black, $white)
	{
	}

################################################################################
# pdf_set_border_style — Set border style of annotations [deprecated]
# pdf_set_border_style ( resource $pdf , string $style , float $width ) : bool
# Sets the border style for all kinds of annotations.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 6, use the options borderstyle and linewidth in PDF_create_annotation() instead.
################################################################################

function pdf_set_border_style(& $pdf, $style, $width)
	{
	}

################################################################################
# pdf_set_char_spacing — Set character spacing [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_value() with parameter charspacing instead.
################################################################################

function pdf_set_char_spacing(& $pdf, $space)
	{
	pdf_set_value($pdf, "charspacing", $space);
	}

################################################################################
# pdf_set_duration - Set duration between pages [deprecated]
# This function is deprecated since PDFlib version 3, use the duration option in PDF_begin_page_ext() or PDF_end_page_ext() instead.
################################################################################

function pdf_set_duration(& $pdf, $duration)
	{
	# check pointer
	if(sscanf($pdf["active"], "%d %d R", $page_id, $page_version) != 2)
		die(__FUNCTION__ . ": invalid page.");

	# set duration
	$pdf["objects"][$page_id]["dictionary"]["/Dur"] = $duration;
	}

################################################################################
# pdf_set_gstate - Activate graphics state object
# pdf_set_gstate ( resource $pdf , int $gstate ) : bool
# Activates a graphics state object.
################################################################################

function pdf_set_gstate(& $pdf, $gstate)
	{
	}

################################################################################
# pdf_set_horiz_scaling - Set horizontal text scaling [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_value() with parameter horizscaling instead.
################################################################################

function pdf_set_horizontal_scaling(& $pdf, $value)
	{
	pdf_set_value($pdf, "horizscaling", $value);
	}

################################################################################
# pdf_set_info - Fill document info field
# pdf_set_info ( resource $pdf , string $key , string $value ) : bool
# Fill document information field key with value.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_set_info(& $pdf, $key, $value)
	{
	$table = [
		"Author",
		"Creator",
		"Keywords",
		"Subject",
		"Title"
		];

	# check if option can be set
	if(! in_array($key, $table))
		die(__FUNCTION__ . ": invalid key: " . $key);

	# convert to iso
	$value = utf8_decode($value);

	$pdf["info"]["/" . $key] = sprintf("(%s)", $value);
	}

################################################################################
# pdf_set_info_author - Fill the author document info field [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_info() instead.
################################################################################

function pdf_set_info_author(& $pdf, $value)
	{
	pdf_set_info($pdf, "Author", $value);
	}

################################################################################
# pdf_set_info_creator - Fill the creator document info field [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_info() instead.
################################################################################

function pdf_set_info_creator(& $pdf, $value)
	{
	pdf_set_info($pdf, "Creator", $value);
	}

################################################################################
# pdf_set_info_keywords - Fill the keywords document info field [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_info() instead.
################################################################################

function pdf_set_info_keywords(& $pdf, $value)
	{
	pdf_set_info($pdf, "Keywords", $value);
	}

################################################################################
# pdf_set_info_subject - Fill the subject document info field [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_info() instead.
################################################################################

function pdf_set_info_subject(& $pdf, $value)
	{
	pdf_set_info($pdf, "Subject", $value);
	}

################################################################################
# pdf_set_info_title - Fill the title document info field [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_info() instead.
################################################################################

function pdf_set_info_title(& $pdf, $value)
	{
	pdf_set_info($pdf, "Title", $value);
	}

################################################################################
# pdf_set_layer_dependency - Define relationships among layers
# pdf_set_layer_dependency ( resource $pdf , string $type , string $optlist ) : bool
# Defines hierarchical and group relationships among layers.
# Returns TRUE on success or FALSE on failure.
# This function requires PDF 1.5.
################################################################################

function pdf_set_layer_dependency(& $pdf, $type, $optlist = [])
	{
	}

################################################################################
# pdf_set_leading - Set distance between text lines [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_value() with the parameter leading instead.
################################################################################

function pdf_set_leading(& $pdf, $distance)
	{
	pdf_set_value($pdf, "leading", $distance);
	}

################################################################################
# pdf_set_parameter - Set string parameter
# pdf_set_parameter ( resource $pdf , string $key , string $value ) : bool
# Sets some PDFlib parameter with string type.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_set_parameter(& $pdf, $key, $value)
	{
	$table = [
		"filename"
		];

	# check if option can be set
	if(! in_array($key, $table))
		die(__FUNCTION__ . ": invalid key: " . $key);

#	if(! isset($pdf[$key]))
#		die(__FUNCTION__ . ": invalid key.");

	# remove some disturbing characters
	$value = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $value);

	$pdf[$key] = $value;
	}

################################################################################
# pdf_set_text_pos - Set text position
# pdf_set_text_pos ( resource $pdf , float $x , float $y ) : bool
# Sets the position for text output on the page.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_set_text_pos(& $pdf, $x, $y)
	{
	# used by pdf_show_boxed:
	#  BT
	#  pdf_set_text_pos
	#  pdf_show | pdf_continue_text
	#  ET

	$pdf["stream"][] = sprintf("%f %f Td", $x, $y);
	}

################################################################################
# pdf_set_text_rendering - Determine text rendering [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_value() with the textrendering parameter instead.
################################################################################

function pdf_set_text_rendering(& $pdf, $textrendering)
	{
	pdf_set_value($pdf, "textrendering", $textrendering);
	}

################################################################################
# pdf_set_text_rise - Set text rise [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_value() with the textrise parameter instead.
################################################################################

function pdf_set_text_rise(& $pdf, $textrise)
	{
	pdf_set_value($pdf, "textrise", $textrise);
	}

################################################################################
# pdf_set_value - Set numerical parameter
# pdf_set_value ( resource $pdf , string $key , float $value ) : bool
# Sets the value of some PDFlib parameter with numerical type.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_set_value(& $pdf, $key, $value)
	{
	# this should depend on push and pop of $pdf["state"]
	$table = [
		"charspacing" => "Tc",
		"horizscaling" => "Tz",
		"leading" => "TL",
		"linecap" => "J",
		"linejoin" => "j",
		"linewidth" => "w",
		"miterlimit" => "M",
		"textrendering" => "Tr",
		"textrise" => "Ts",
		"wordspacing" => "Tw"
		];

	# check if option can be set
	if(! isset($table[$key]))
		die(__FUNCTION__ . ": invalid key: " . $key);

	# check if value is numeric
	if(! is_numeric($value))
		die(__FUNCTION__ . ": invalid value: " . $value);

	$pdf["stream"][] = sprintf("%f %s", $value, $table[$key]);
	}

################################################################################
# pdf_set_word_spacing - Set spacing between words [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_set_value() with the wordspacing parameter instead.
################################################################################

function pdf_set_word_spacing(& $pdf, $wordspacing)
	{
	pdf_set_value($pdf, "wordspacing", $wordspacing);
	}

################################################################################
# pdf_setcolor - Set fill and stroke color
# pdf_setcolor ( resource $pdf , string $fstype , string $colorspace , float $c1 , float $c2 , float $c3 , float $c4 ) : bool
# Sets the current color space and color.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setcolor(& $pdf, $fstype, $colorspace, $c1, $c2 = 0, $c3 = 0, $c4 = 0)
	{
	if(($fstype == "fill") && ($colorspace == "gray"))
		return($pdf["stream"][] = sprintf("%f g", $c1));

	if(($fstype == "fill") && ($colorspace == "rgb"))
		return($pdf["stream"][] = sprintf("%f %f %f rg", $c1, $c2, $c3));

	if(($fstype == "fill") && ($colorspace == "cmyk"))
		return($pdf["stream"][] = sprintf("%f %f %f %f k", $c1, $c2, $c3, $c4));

	if(($fstype == "stroke") && ($colorspace == "gray"))
		return($pdf["stream"][] = sprintf("%f G", $c1));

	if( ($fstype == "stroke") &&($colorspace == "rgb"))
		return($pdf["stream"][] = sprintf("%f %f %f RG", $c1, $c2, $c3));

	if(($fstype == "stroke") && ($colorspace == "cmyk"))
		return($pdf["stream"][] = sprintf("%f %f %f %f K", $c1, $c2, $c3, $c4));
	}

################################################################################
# pdf_setdash - Set simple dash pattern
# pdf_setdash ( resource $pdf , float $b , float $w ) : bool
# Sets the current dash pattern to b black and w white units.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setdash(& $pdf, $b, $w)
	{
	$pdf["stream"][] = sprintf("%f %f d", $b, $w);
	}

################################################################################
# pdf_setdashpattern - Set dash pattern
# pdf_setdashpattern ( resource $pdf , string $optlist ) : bool
# Sets a dash pattern defined by an option list.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setdashpattern(& $pdf, $optlist = [])
	{
	}

################################################################################
# pdf_setflat - Set flatness
# pdf_setflat ( resource $pdf , float $flatness ) : bool
# Sets the flatness parameter.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setflat(& $pdf, $flatness)
	{
	$pdf["stream"][] = sprintf("%f i", $flatness);
	}

################################################################################
# pdf_setfont - Set font
# pdf_setfont ( resource $pdf , int $font , float $fontsize ) : bool
# Sets the current font in the specified fontsize, using a font handle returned by PDF_load_font().
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setfont(& $pdf, $font, $fontsize)
	{
	# workaround to allow fontname (Helvetica) instead of its alias (/Fx)
	if(sscanf($font, "/F%d", $whatever) != 1)
		$font = pdf_findfont($pdf, $font, "winansi", 1);

	# one step above the alias was checked
	if(sscanf($font, "/F%d", $iwhatever) != 1)
		die(__FUNCTION__ . ": invalid font.");

	# check if font is loaded
	if(! isset($pdf["resources"]["/Font"][$font]))
		die(__FUNCTION__ . ": font not found.");

	# check if pointer of loaded font is valid
	if(sscanf($pdf["resources"]["/Font"][$font], "%d %d R", $object_id, $object_version) != 2)
		die(__FUNCTION__ . ": invalid pointer.");

	# check if pointer of page is valid
	if(sscanf($pdf["active"], "%d %d R", $page_id, $page_version) != 2)
		die(__FUNCTION__ . ": invalid pointer.");

	# remember font as used resource
	$pdf["objects"][$page_id]["dictionary"]["/Resources"]["/Font"][$font] = $pdf["resources"]["/Font"][$font];
	
	# update internals (used by pdf_stringwidth)
	$pdf["font"] = $font;
	$pdf["fontsize"] = $fontsize;

	# finally
	$pdf["stream"][] = sprintf("%s %f Tf", $font, $fontsize);
	}

################################################################################
# pdf_setgray - Set color to gray [deprecated]
# pdf_setgray ( resource $pdf , float $g ) : bool
# Sets the current fill and stroke color to a gray value between 0 and 1 inclusive.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 4, use PDF_setcolor() instead.
################################################################################

function pdf_setgray(& $pdf, $g)
	{
	pdf_setcolor($pdf, "fill", "gray", $g);
	pdf_setcolor($pdf, "stroke", "gray", $g);
	}

################################################################################
# pdf_setgray_fill - Set fill color to gray [deprecated]
# pdf_setgray_fill ( resource $pdf , float $g ) : bool
# Sets the current fill color to a gray value between 0 and 1 inclusive.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 4, use PDF_setcolor() instead.
################################################################################

function pdf_setgray_fill(& $pdf, $g)
	{
	pdf_setcolor($pdf, "fill", "gray", $g);
	}

################################################################################
# pdf_setgray_stroke - Set stroke color to gray [deprecated]
# pdf_setgray_stroke ( resource $pdf , float $g ) : bool
# Sets the current stroke color to a gray value between 0 and 1 inclusive.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 4, use PDF_setcolor() instead.
################################################################################

function pdf_setgray_stroke(& $pdf, $g)
	{
	pdf_setcolor($pdf, "stroke", "gray", $g);
	}

################################################################################
# pdf_setlinecap - Set linecap parameter
# pdf_setlinecap ( resource $pdf , int $linecap ) : bool
# Sets the linecap parameter to control the shape at the end of a path with respect to stroking.
################################################################################

function pdf_setlinecap(& $pdf, $linecap)
	{
	pdf_set_value($pdf, "linecap", $linecap);
	}

################################################################################
# pdf_setlinejoin - Set linejoin parameter
# pdf_setlinejoin ( resource $pdf , int $value ) : bool
# Sets the linejoin parameter to specify the shape at the corners of paths that are stroked.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setlinejoin(& $pdf, $value)
	{
	pdf_set_value($pdf, "linejoin", $value);
	}

################################################################################
# pdf_setlinewidth - Set line width
# pdf_setlinewidth ( resource $pdf , float $width ) : bool
# Sets the current line width.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setlinewidth(& $pdf, $width)
	{
	pdf_set_value($pdf, "linewidth", $width);
	}

################################################################################
# pdf_setmatrix - Set current transformation matrix
# pdf_setmatrix ( resource $pdf , float $a , float $b , float $c , float $d , float $e , float $f ) : bool
# Explicitly sets the current transformation matrix.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setmatrix(& $pdf, $a, $b, $c, $d, $e, $f)
	{
	$pdf["stream"][] = sprintf("%f %f %f %f %f %f Tm", $a, $b, $c, $d, $e, $f);
	}

################################################################################
# pdf_setmiterlimit - Set miter limit
# pdf_setmiterlimit ( resource $pdf , float $miter ) : bool
# Sets the miter limit.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_setmiterlimit(& $pdf, $miter)
	{
	pdf_set_value($pdf, "miterlimit", $miter);
	}

################################################################################
# pdf_setpolydash - Set complicated dash pattern [deprecated]
# This function is deprecated since PDFlib version 5, use PDF_setdashpattern() instead.
################################################################################

function pdf_setpolydash(& $pdf, $dash)
	{
	pdf_setdashpattern($pdf, $dash);
	}

################################################################################
# pdf_setrgbcolor - Set fill and stroke rgb color values [deprecated]
# pdf_setrgbcolor ( resource $pdf , float $red , float $green , float $blue ) : bool
# Sets the current fill and stroke color to the supplied RGB values.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 4, use PDF_setcolor() instead.
################################################################################

function pdf_setrgbcolor(& $pdf, $red, $green, $blue)
	{
	pdf_setcolor($pdf, "fill", "rgb", $red, $green, $blue);
	pdf_setcolor($pdf, "stroke", "rgb", $red, $green, $blue);
	}

################################################################################
# pdf_setrgbcolor_fill - Set fill rgb color values [deprecated]
# pdf_setrgbcolor_fill ( resource $pdf , float $red , float $green , float $blue ) : bool
# Sets the current fill color to the supplied RGB values.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 4, use PDF_setcolor() instead.
################################################################################

function pdf_setrgbcolor_fill(& $pdf, $red, $green, $blue)
	{
	pdf_setcolor($pdf, "fill", "rgb", $red, $green, $blue);
	}

################################################################################
# pdf_setrgbcolor_stroke - Set stroke rgb color values [deprecated]
# pdf_setrgbcolor_stroke ( resource $pdf , float $red , float $green , float $blue ) : bool
# Sets the current stroke color to the supplied RGB values.
# Returns TRUE on success or FALSE on failure.
# This function is deprecated since PDFlib version 4, use PDF_setcolor() instead.
################################################################################

function pdf_setrgbcolor_stroke(& $pdf, $red, $green, $blue)
	{
	pdf_setcolor($pdf, "stroke", "rgb", $red, $green, $blue);
	}

################################################################################
# pdf_set_text_matrix - Set text matrix [deprecated]
# This function is deprecated since PDFlib version 3, use PDF_scale(), PDF_translate(), PDF_rotate(), or PDF_skew() instead.
################################################################################

function pdf_settext_matrix(& $pdf, $a, $b, $c, $d, $e, $f)
	{
	$pdf["stream"][] = sprintf("%f %f %f %f %f %f Tm", $a, $b, $c, $d, $e, $f);
	}

################################################################################
# pdf_shading - Define blend
# pdf_shading ( resource $pdf , string $shtype , float $x0 , float $y0 , float $x1 , float $y1 , float $c1 , float $c2 , float $c3 , float $c4 , string $optlist ) : int
# Defines a blend from the current fill color to another color.
# This function requires PDF 1.4 or above.
################################################################################

function pdf_shading(& $pdf, $shtype, $x0, $y0, $x1, $y1, $c1, $c2, $c3, $c4, $optlist = [])
	{
	}

################################################################################
# pdf_shading_pattern - Define shading pattern
# pdf_shading_pattern ( resource $pdf , int $shading , string $optlist ) : int
# Defines a shading pattern using a shading object.
# This function requires PDF 1.4 or above.
################################################################################

function pdf_shading_pattern(& $pdf, $shading, $optlist = [])
	{
	}

################################################################################
# pdf_shfill - Fill area with shading
# pdf_shfill ( resource $pdf , int $shading ) : bool
# Fills an area with a shading, based on a shading object.
# This function requires PDF 1.4 or above.
################################################################################

function pdf_shfill(& $pdf, $shading)
	{
	$pdf["stream"][] = sprintf("/S%d sh", $shading);
	}

################################################################################
# pdf_show - Output text at current position
# pdf_show ( resource $pdf , string $text ) : bool
# Prints text in the current font and size at the current position.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_show(& $pdf, $text)
	{
	# used by pdf_show_boxed:
	#  BT
	#  pdf_set_text_pos
	#  pdf_show | pdf_continue_text
	#  ET

	# check text
	if(! strlen($text))
		return;

	# remove some disturbing characters
	$text = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);

	$pdf["stream"][] = sprintf("(%s) Tj", $text);
	}

################################################################################
# pdf_show_boxed - Output text in a box [deprecated]
# pdf_show_boxed ( resource $pdf , string $text , float $left , float $top , float $width , float $height , string $mode , string $feature ) : int
# This function is deprecated since PDFlib version 6, use PDF_fit_textline() for single lines, or the PDF_*_textflow() functions for multi-line formatting instead.
################################################################################

function pdf_show_boxed(& $pdf, $text, $left, $top, $width, $height, $mode, $feature = [])
	{
	# check text
	if(! strlen($text))
		return(0);

	$font = pdf_get_value($pdf, "font", 0);
	$fontsize = pdf_get_value($pdf, "fontsize", 0);

	# check border
	if(isset($feature["border"]))
		pdf_rect($pdf, $left, $top, $width, $height);

	# check border
	if(isset($feature["border"]))
		pdf_stroke($pdf);

	# check leading
	if(isset($feature["leading"]))
		$leading = $feature["leading"];
	else
		$leading = $fontsize;

	while($text)
		{
		if($height < $fontsize)
			break;

		list($line, $text) = (strpos($text, PHP_EOL) === false ? [$text, ""] : explode(PHP_EOL, $text, 2));

		$words = "";

		while($line)
			{
			list($word, $line) = (strpos($line, " ") === false ? [$line, ""] : explode(" ", $line, 2));

			if(! strlen($word))
				$test = $words;
			elseif(! strlen($words))
				$test = $word;
			else
				$test = $words . " " . $word;

			if(pdf_stringwidth($pdf, $test, $font, $fontsize) > $width)
				{
				if(strlen($word))
					if(strlen($line))
						$line = $word . " " . $line;
					else
						$line = $word;

				if(strlen($line))
					if(strlen($text))
						$text = $line . PHP_EOL . $text;
					else
						$text = $line;

				break;
				}

			$words = $test;
			}

		$spacing = $width - pdf_stringwidth($pdf, $words, $font, $fontsize);

		if(($mode == "justify") || ($mode == "fulljustify"))
			{
			pdf_set_word_spacing($pdf, $spacing / (count(explode(" ", $words)) - 1));

			$spacing = 0;
			}
		else
			{
			$modes = ["center" => 2, "right" => 1];

			$spacing = (array_key_exists($mode, $modes) ? $spacing / $modes[$mode] : 0);
			}

		pdf_show_xy($pdf, $words, $left + $spacing, $top);

		$top -= $leading;
		$height -= $leading;
		}

	# return length of remaining text
	return(strlen($text));
	}

################################################################################
# pdf_show_xy - Output text at given position
# pdf_show_xy ( resource $pdf , string $text , float $x , float $y ) : bool
# Prints text in the current font.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_show_xy(& $pdf, $text, $x, $y)
	{
	# check text
	if(! strlen($text))
		return;

	# convert to iso
	$text = utf8_decode($text);

	# remove some disturbing characters
	$text = str_replace(["\\", "(", ")"], ["\\\\", "\\(", "\\)"], $text);

	$pdf["stream"][] = "BT";
	$pdf["stream"][] = sprintf("%f %f Td", $x, $y); # pdf_set_text_pos
	$pdf["stream"][] = sprintf("(%s) Tj", $text); # pdf_show
	$pdf["stream"][] = "ET";
	}

################################################################################
# pdf_skew - Skew the coordinate system
# pdf_skew ( resource $pdf , float $alpha , float $beta ) : bool
# Skews the coordinate system in x and y direction by alpha and beta degrees, respectively.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_skew(& $pdf, $alpha, $beta)
	{
	$alpha = tan($alpha * M_PI / 180); # deg 2 rad
	$beta = tan($beta * M_PI / 180); # deg 2 rad

	pdf_concat($pdf, 1, $alpha, $beta, 1, 0, 0);
	}

################################################################################
# pdf_stringwidth - Return width of text
# pdf_stringwidth ( resource $pdf , string $text , int $font , float $fontsize ) : float
# Returns the width of text in an arbitrary font.
################################################################################

function pdf_stringwidth(& $pdf, $text, $font, $fontsize)
	{
	# check text
	if(! strlen($text))
		return(0);

	# check if font is valid
	if(sscanf($font, "/F%d", $whatever) != 1)
		die(__FUNCTION__ . ": invalid font: " . $font);

	# check if fontsize is valid
	if($fontsize == 0)
		die(__FUNCTION__ . ": invalid fontsize: " . $fontsize);

	# check if font is loaded
	if(! isset($pdf["resources"]["/Font"][$font]))
		die(__FUNCTION__ . ": font not found: " . $font);

	# check if pointer is valid
	if(sscanf($pdf["resources"]["/Font"][$font], "%d %d R", $object_id, $object_version) != 2)
		die(__FUNCTION__ . ": invalid pointer.");

	# set counter
	$width = 0;

	# convert to iso
	$text = utf8_decode($text);

	# count width of chars
	foreach(str_split($text) as $char)
		$width += $pdf["objects"][$object_id]["dictionary"]["/Widths"][ord($char)];

	return($width / 1000 * $fontsize);
	}

################################################################################
# pdf_stroke - Stroke path
# pdf_stroke ( resource $pdf ) : bool
# Strokes the path with the current color and line width, and clear it.
# Returns TRUE on success or FALSE on failure.
################################################################################

function pdf_stroke(& $pdf)
	{
	$pdf["stream"][] = "S";
	}

################################################################################
# pdf_suspend_page - Suspend page
# pdf_suspend_page ( resource $pdf , string $optlist ) : bool
# Suspends the current page so that it can later be resumed with PDF_resume_page().
################################################################################

function pdf_suspend_page(& $pdf, $optlist = [])
	{
	if(! isset($optlist["active"]))
		die(__FUNCTION__ . ": page must be set as option.");

	if(sscanf($optlist["active"], "%d %d R", $page_id, $page_version) != 2)
		die(__FUNCTION__ . ": invalid page.");

	if(! isset($pdf["objects"][$page_id]["dictionary"]["/Contents"]))
		die(__FUNCTION__ . ": contents not found.");

	if(sscanf($pdf["objects"][$page_id]["dictionary"]["/Contents"], "%d %d R", $contents_id, $contents_version) != 2)
		die(__FUNCTION__ . ": invalid contents.");

	if(! isset($pdf["objects"][$contents_id]["stream"]))
		die(__FUNCTION__ . ": stream not found.");

	# save stream
	$pdf["objects"][$page_id]["stream"] = implode(PHP_EOL, $pdf["stream"]);

	# be prepared
	$pdf["stream"] = [];
	}

################################################################################
# pdf_translate - Set origin of coordinate system
# pdf_translate ( resource $pdf , float $tx , float $ty ) : bool
# Translates the origin of the coordinate system.
################################################################################

function pdf_translate(& $pdf, $tx, $ty)
	{
	pdf_concat($pdf, 1, 0, 0, 1, $tx, $ty);
	}

################################################################################
# pdf_utf8_to_utf16 - Convert string from UTF-8 to UTF-16
# pdf_utf8_to_utf16 ( resource $pdf , string $utf8string , string $ordering ) : string
# Converts a string from UTF-8 format to UTF-16.
################################################################################

function pdf_utf8_to_utf16(& $pdf, $utf8string, $ordering)
	{
	foreach($ordering as $key => $value)
		iconv_set_encoding($key, $value);

	return(iconv("UTF-8", "UTF-16", $utf8string));
	}

################################################################################
# pdf_utf16_to_utf8 - Convert string from UTF-16 to UTF-8
# pdf_utf16_to_utf8 ( resource $pdf , string $utf16string ) : string
# Converts a string from UTF-16 format to UTF-8.
################################################################################

function pdf_utf16_to_utf8(& $pdf, $utf16string)
	{
	return(iconv("UTF-16", "UTF-8", $utf16string));
	}

################################################################################
# pdf_utf32_to_utf16 - Convert string from UTF-32 to UTF-16
# pdf_utf32_to_utf16 ( resource $pdf , string $utf32string , string $ordering ) : string
# Converts a string from UTF-32 format to UTF-16.
################################################################################

function pdf_utf32_to_utf16(& $pdf, $utf32string, $ordering)
	{
	foreach($ordering as $key => $value)
		iconv_set_encoding($key, $value);

	return(iconv("UTF-32", "UTF-16", $utf32string));
	}

################################################################################
# ...
################################################################################

function _pdf_read_chr($handle)
	{
	$retval = _pdf_read_str($handle, 1);

	return(ord($retval));
	}

function _pdf_read_lng($handle)
	{
	$retval = unpack("Ni", _pdf_read_str($handle, 4));

	return($retval["i"]);
	}

function _pdf_read_str($handle, $length)
	{
	$retval = "";

	while(($length > 0) && (! feof($handle)))
		{
		$chunk = fread($handle, $length);

		if(! $chunk)
			die(__FUNCTION__ . ": error while reading stream.");

		$length -= strlen($chunk);
		$retval .= $chunk;
		}

	if($length)
		die(__FUNCTION__ . ": unexpected end of stream.");

	return($retval);
	}

################################################################################
# _pdf_add_font_definiton ( array $pdf , string $encoding , string $differences , ... ) : string
# pending
################################################################################

function _pdf_add_font_definition(& $pdf, $encoding = "builtin", $differences = "")
	{
	$encoding = _pdf_add_font_encoding($pdf, $encoding, $differences);

	$stream = _pdf_add_page_stream($pdf, "");

	$font_descriptor = _pdf_add_font_descriptor($pdf, "test"); # no file

	# create new object id
	$whatever_id = _pdf_get_free_object_id($pdf);
	$whatever_version = 0;

	# create new object (font)
	$pdf["objects"][$whatever_id] = [
		"id" => $whatever_id,
		"version" => $whatever_version,
		"dictionary" => [
			"/Type" => "/Font",
			"/Subtype" => "/Type3",
			"/FontBBox" => "[0 0 1000 1000]",
			"/FontMatrix" => "[1 0 0 -1 0 0]",
			"/CharProcs" => sprintf("<< %s %s %s %s %s %s >>", "/C", $stream, "/B", $stream, "/A", $stream),
			"/Encoding" => $encoding,
			"/FirstChar" => 65,
			"/LastChar" => 67,
			"/Widths" => "[8 8 8]",
			"/FontDescriptor" => $font_descriptor
			]
		];

	return(sprintf("%d %d R", $whatever_id, $vatever_version));
	}

################################################################################
# _pdf_add_font_descriptor ( array $pdf , string $fontname , string $fontfile ) : string
################################################################################

function _pdf_add_font_descriptor(& $pdf, $fontname, $fontfile = "")
	{
	# create new object id
	$whatever_id = _pdf_get_free_object_id($pdf);
	$whatever_version = 0;

	# create new object (font descriptor)
	$pdf["objects"][$whatever_id] = [
		"id" => $whatever_id,
		"version" => $whatever_version,
		"dictionary" => [
			"/Type" => "/FontDescriptor",
			"/FontName" => "/" . $fontname,
			"/Flags" => FONTDESCRIPTOR_FLAG_SERIF | FONTDESCRIPTOR_FLAG_SCRIPT,
			"/FontBBox" => "[0 -240 1440 1000]",
			"/ItalicAngle" => 0,
			"/Ascent" => 720,
			"/Descent" => 0 - 250,
			"/CapHeight" => 720,
			"/StemV" => 90
			]
		];

	# apply location of fontfile
	if($fontfile)
		$pdf["objects"][$whatever_id]["dictionary"]["/FontFile2"] = $fontfile;

	# return new object id
	return(sprintf("%d %d R", $whatever_id, $whatever_version));
	}

################################################################################
# _pdf_add_font_encoding ( array $pdf , string $differences ) : string
################################################################################

function _pdf_add_font_encoding(& $pdf, $encoding = "builtin", $differences = "") # make differences an optlist
	{
	# check encoding
	if(! in_array($encoding, ["builtin", "winansi", "macroman", "macexpert"]))
		die(__FUNCTION__ . ": invalid encoding: " . $encoding);

	# create new object id
	$whatever_id = _pdf_get_free_object_id($pdf);
	$whatever_version = 0;

	# create new object (encoding)
	$pdf["objects"][$whatever_id] = [
		"id" => $whatever_id,
		"version" => $whatever_version,
		"dictionary" => [
			"/Type" => "/Encoding"
			]
		];

	# apply differences
	if($differences)
		$pdf["objects"][$whatever_id]["dictionary"]["/Differences"] = $differences;

	# valid encodings
	$encodings = ["winansi" => "/WinAnsiEncoding", "macroman" => "/MacRomanEncoding", "macexpert" => "/MacExpertEncoding"];

	# apply encoding
	if($encoding != "builtin") # /StandardEncoding
		if(isset($encodings[$encoding]))
			$pdf["objects"][$whatever_id]["dictionary"]["/BaseEncoding"] = $encodings[$encoding];
		else
			$pdf["objects"][$whatever_id]["dictionary"]["/BaseEncoding"] = $encoding;

	# return new object id
	return(sprintf("%d %d R", $whatever_id, $whatever_version));
	}

################################################################################
# _pdf_add_form ( array $pdf , string $bbox , string $resources , string $stream ) : string
################################################################################

function _pdf_add_form(& $pdf, $resources, $bbox, $stream)
	{
	# check resources for beeing dictionary or pointer to such

	if(sscanf($bbox, "[%d %d %d %d]", $x, $y, $w, $h) != 4)
		die(__FUNCTION__ . ": invalid bbox:" . $bbox);

	# create new object id
	$whatever_id = _pdf_get_free_object_id($pdf);
	$whatever_version = 0;

	# create new object (form)
	$pdf["objects"][$whatever_id] = [
		"id" => $whatever_id,
		"version" => $whatever_version,
		"dictionary" => [
			"/Type" => "/XObject",
			"/Subtype" => "/Form",
			"/FormType" => 1,
			"/Resources" => $resources,
			"/BBox" => $bbox,
			"/Length" => strlen($stream)
			],
		"stream" => $stream
		];

	# return new object id
	return(sprintf("%d %d R", $whatever_id, $whatever_version));
	}

################################################################################
# _pdf_add_stream ( array $pdf , string $stream , array $optlist ) : string
################################################################################

function _pdf_add_stream(& $pdf, $stream, $optlist = [])
	{
	# create new object id
	$whatever_id = _pdf_get_free_object_id($pdf);
	$whatever_version = 0;

	# create new object (default stream of no type)
	$pdf["objects"][$whatever_id] = [
		"id" => $whatever_id,
		"version" => $whatever_version,
		"dictionary" => [
			"/Length" => strlen($stream)
			],
		"stream" => $stream
		];

	# apply additional settings to created object
	foreach($optlist as $key => $value)
		$pdf["objects"][$whatever_id]["dictionary"][$key] = $value;

	# return new object id
	return(sprintf("%d %d R", $whatever_id, $whatever_version));
	}

################################################################################
# _pdf_filter_ascii85_decode ( string $value ) : string
################################################################################

function _pdf_filter_ascii85_decode($value)
	{
	$return = "";

	$base = [];

	foreach(range(0, 4) as $i)
		$base[$i] = pow(85, $i);

	foreach(str_split($value, 5) as $tuple)
		{
		if($tuple === "zzzzz")
			{
			$return .= str_repeat(chr(0), 4);

			continue;
			}

		$bin_tuple = "0";

		$len = strlen($tuple);

		$tuple .= str_repeat("u", 5 - $len);

		foreach(range(0, 4) as $i)
			$bin_tuple += ((ord($tuple[$i]) - 33) * $base[4 - $i]);

		$i = 4;

		$tuple = "";

		$len -= 1;

		while($len --)
			$tuple .= chr((bindec(sprintf("%032b", $bin_tuple)) >> (-- $i * 8)) & 0xFF);

		$return .= $tuple;
		}

	return($return);
	}

################################################################################
# _pdf_filter_ascii85_encode ( string $value ) : string
################################################################################

function _pdf_filter_ascii85_encode($string)
	{
	$return = "";

	foreach(str_split($string, 4) as $tuple)
		{
		$binary = 0;

		for($i = 0; $i < strlen($tuple); $i ++)
			$binary |= (ord($tuple[$i]) << ((3 - $i) * 8));

		$tuple = "";

		foreach(range(0, 4) as $i)
			{
			$tuple = chr($binary % 85 + 33) . $tuple;

			$binary /= 85;
			}

		$return .= substr($tuple, 0, strlen($tuple) + 1);;
		}

	return($return);
	}

################################################################################
# _pdf_filter_asciihex_decode ( string $value ) : string
################################################################################

function _pdf_filter_asciihex_decode($data)
	{
	return(hex2bin($data));
	}

################################################################################
# _pdf_filter_asciihex_encode ( string $value ) : string
################################################################################

function _pdf_filter_asciihex_encode($data)
	{
	return(bin2hex($data));
	}

################################################################################
# _pdf_filter_change ( array $pdf , string $filter ) : array
################################################################################

function _pdf_filter_change(& $pdf, $filter = "")
	{
	foreach($pdf["objects"] as $index => $object)
		{
		# null-object as trailer
		if($index == 0)
			continue;

		if(! isset($object["stream"]))
			continue;

		if(isset($object["dictionary"]["/Filter"]))
			list($filter_old, $null) = _pdf_filter_parse($object["dictionary"]["/Filter"]);
		else
			$filter_old = [];

		$data = $object["stream"];

		while(1)
			{
			if(! $filter_old)
				break;

			if($filter_old[0] == "/ASCII85Decode")
				$data = _pdf_filter_ascii85_decode($data);

			if($filter_old[0] == "/ASCIIHexDecode")
				$data = _pdf_filter_asciihex_decode($data);

			if($filter_old[0] == "/DCTDecode")
				break; # image

			if($filter_old[0] == "/FlateDecode")
				$data = _pdf_filter_flate_decode($data);

			if($filter_old[0] == "/LZWDecode")
				$data = _pdf_filter_lzw_decode($data);

			$filter_old = array_slice($filter_old, 1);
			}

		$pdf["objects"][$index]["stream"] = $data;
		$pdf["objects"][$index]["dictionary"]["/Length"] = strlen($data);

		if(! $filter_old)
			unset($pdf["objects"][$index]["dictionary"]["/Filter"]);
		elseif(count($filter_old) == 1)
			$pdf["objects"][$index]["dictionary"]["/Filter"] = sprintf("%s", _pdf_glue_array($filter_old));
		else
			$pdf["objects"][$index]["dictionary"]["/Filter"] = sprintf("[%s]", _pdf_glue_array($filter_old));
		}
	
	################################################################################

	foreach($pdf["objects"] as $index => $object)
		{
		# null-object as trailer
		if($index == 0)
			continue;

		if(! isset($object["stream"]))
			continue;

		if(isset($object["dictionary"]["/Filter"]))
			list($filter_old, $null) = _pdf_filter_parse($object["dictionary"]["/Filter"]);
		else
			$filter_old = [];

		list($filter_new, $null) = _pdf_filter_parse($filter);

		$filter_new = array_reverse($filter_new);

		$data = $object["stream"];

		while(1)
			{
			if(! $filter_new)
				break;

			if($filter_new[0] == "/ASCII85Decode")
				$data = _pdf_filter_ascii85_encode($pdf["objects"][$index]["stream"]);

			if($filter_new[0] == "/ASCIIHexDecode")
				$data = _pdf_filter_asciihex_encode($data);

			if($filter_new[0] == "/FlateDecode")
				$data = _pdf_filter_flate_encode($data);

			if($filter_new[0] == "/LZWDecode")
				$data = _pdf_filter_lzw_encode($data);

			$filter_old = array_merge([$filter_new[0]], $filter_old);

			$filter_new = array_slice($filter_new, 1);
			}

		$pdf["objects"][$index]["stream"] = $data;
		$pdf["objects"][$index]["dictionary"]["/Length"] = strlen($data);

		if(! $filter_old)
			unset($pdf["objects"][$index]["dictionary"]["/Filter"]);
		elseif(count($filter_old) == 1)
			$pdf["objects"][$index]["dictionary"]["/Filter"] = sprintf("%s", _pdf_glue_array($filter_old));
		else
			$pdf["objects"][$index]["dictionary"]["/Filter"] = sprintf("[%s]", _pdf_glue_array($filter_old));
		}

	return(true);
	}

################################################################################
# _pdf_filter_flate_encode ( string $value ) : string
################################################################################

function _pdf_filter_flate_encode($data)
	{
	# there must be something more difficult
	return(gzcompress($data, 9));
	}

################################################################################
# _pdf_filter_flate_decode ( string $value ) : string
################################################################################

function _pdf_filter_flate_decode($data)
	{
	# there must be something more difficult
	return(gzuncompress($data));
	}

################################################################################
# _pdf_filter_lzw_decode ( string $value ) : string
################################################################################

function _pdf_filter_lzw_decode($binary)
	{
	# something more difficult ... here it is
	$dictionary_count = 256;
	$bits = 8;
	$codes = [];
	$rest = 0;
	$rest_length = 0;

	for($i = 0; $i < strlen($binary); $i ++)
		{
		$rest = ($rest << 8) + ord($binary[$i]);
		$rest_length += 8;

		if($rest_length >= $bits)
			{
			$rest_length -= $bits;
			$codes[] = $rest >> $rest_length;
			$rest = $rest & ((1 << $rest_length) - 1);
			$dictionary_count ++;

			if($dictionary_count >> $bits)
				$bits ++;
			}
		}

	$dictionary = range("\x00", "\xFF");
	$return = "";

	foreach($codes as $i => $code)
		{
		if(isset($dictionary[$code]))
			$element = $dictionary[$code];
		else
			$element = $word . $word[0];

		$return .= $element;

		if($i > 0)
			$dictionary[] = $word . $element[0];

		$word = $element;
		}

	return($return);
	}

################################################################################
# _pdf_filter_lzw_encode ( string $value ) : string
################################################################################

function _pdf_filter_lzw_encode($string)
	{
	# sorry goes on ... same difficulty level
	$dictionary = array_flip(range("\x00", "\xFF"));
	$word = "";
	$codes = [];

	for($i = 0; $i <= strlen($string); $i ++)
		{
		$x = substr($string, $i, 1);

		if(strlen($x) > 0 && isset($dictionary[$word . $x]))
			$word .= $x;
		elseif($i > 0)
			{
			$codes[] = $dictionary[$word];
			$dictionary[$word . $x] = count($dictionary);
			$word = $x;
			}
		}

	$dictionary_count = 256;
	$bits = 8;
	$return = "";
	$rest = 0;
	$rest_length = 0;

	foreach($codes as $code)
		{
		$rest = ($rest << $bits) + $code;
		$rest_length += $bits;
		$dictionary_count ++;

		if($dictionary_count >> $bits)
			$bits ++;

		while($rest_length > 7)
			{
			$rest_length -= 8;
			$return .= chr($rest >> $rest_length);
			$rest &= ((1 << $rest_length) - 1);
			}
		}

	return($return . ($rest_length > 0 ? chr($rest << (8 - $rest_length)) : ""));
	}

################################################################################
# _pdf_filter_parse ( string $data ) : array
# this function is needed because user can setup /Filter for final writing
################################################################################

function _pdf_filter_parse($data = "")
	{
	# finally ...
	$retval = [];

	while(1)
		{
		if(! $data)
			break;
		elseif($data[0] == " ")
			$data = substr($data, 1);
		elseif($data[0] == "[")
			{
			$data = substr($data, 1);
			list($retval, $data) = _pdf_parse_array($data);
			$data = substr($data, 1);
			}
		elseif($data[0] == "]")
			break;
		elseif($data[0] == "/")
			{
			$data = substr($data, 1);
			list($name, $data) = _pdf_parse_name($data);

			$retval[] = sprintf("/%s", $name);
			}
		else
			die(__FUNCTION__ . ": you should never be here: data follows: " . $data);
		}

	return([$retval, $data]);
	}

################################################################################
# _pdf_filter_rle_decode ( string $value ) : string
################################################################################

function _pdf_filter_rle_decode($data)
	{
	# makes sense for binary ... yeah
	return(preg_replace_callback('/(\d+)(\D)/', function($match) { return(str_repeat($match[2], $match[1])); }, $data));
	}

################################################################################
# _pdf_filter_rle_encode ( string $value ) : string
################################################################################

function _pdf_filter_rle_encode($data)
	{
	# makes sense for binary ... yeah
	return(preg_replace_callback('/(.)\1*/', function($match) { return(strlen($match[0]) . $match[1]); }, $data));
	}

################################################################################
# _pdf_get_free_font_id ( array $pdf ) : int
################################################################################

function _pdf_get_free_font_id(& $pdf, $index_id = 1)
	{
	# check existing global resources
	if(isset($pdf["resources"]["/Font"]))
		while(isset($pdf["resources"]["/Font"]["/F" . $index_id]))
			$index_id ++;

	# return unique id
	return("/F" . $index_id);
	}

################################################################################
# _pdf_get_free_object_id ( array $pdf ) : int
################################################################################

function _pdf_get_free_object_id(& $pdf, $object_id = 1)
	{
	# check existing objects
	if(isset($pdf["objects"]))
		while(isset($pdf["objects"][$object_id]))
			$object_id ++;

	# return unique id
	return($object_id);
	}

################################################################################
# _pdf_get_free_xobject_id ( array $pdf ) : int
################################################################################

function _pdf_get_free_xobject_id(& $pdf, $index_id = 1)
	{
	# check existing global resources
	if(isset($pdf["resources"]["/XObject"]))
		while(isset($pdf["resources"]["/XObject"]["/X" . $index_id]))
			$index_id ++;

	# return unique id
	return("/X" . $index_id);
	}

################################################################################
# _pdf_get_random_font_id ( array $pdf ) : string
################################################################################

function _pdf_get_random_font_id(& $pdf, $fontname)
	{
	# check fontname
	if(sscanf($fontname, "/%s", $fontname) != 1)
		die(__FUNCTION__ . ": invalid fontname.");

	# create default unique id
	$fontname = sprintf("/AAAAAA-%s", $fontname);

	# replace template
	foreach(range(1, 6) as $position)
		$fontname[$position] = chr(rand(65, 90));
	
	# return created unique ad
	return($fontname);
	}

################################################################################
# _pdf_glue_array ( array $array ) : string
# returns $array as string.
################################################################################

function _pdf_glue_array($array, $glue_children = true)
	{
	$retval = [];

	foreach($array as $value)
		{
		if($glue_children && is_array($value))
			$value = sprintf("[%s]", _pdf_glue_array($value));

		$retval[] = sprintf("%s", $value);
		}

	return(implode(" ", $retval));
	}

################################################################################
# _pdf_glue_dictionary ( array $dictionary ) : string
# returns $dictionary as string.
################################################################################

function _pdf_glue_dictionary($dictionary, $glue_children = true)
	{
	$retval = [];

	foreach($dictionary as $key => $value)
		{
		if($glue_children && is_array($value))
			$value = sprintf("<< %s >>", _pdf_glue_dictionary($value));

		$retval[] = sprintf("%s %s", $key, $value);
		}

	return(implode(" ", $retval));
	}

################################################################################
# _pdf_glue_document ( array $objects ) : string
# returns $objects as string (pdf-format).
################################################################################

function _pdf_glue_document($objects, $optional = true)
	{
	# fix count
	$objects[0]["dictionary"]["/Size"] = count($objects); # inclusive null-object

	# header
	$retval = ["%PDF-1.3"];

	# body

	# first entry for null-object
	$offsets = [0];

	foreach($objects as $index => $object)
		{
		# null-object as trailer
		if($index == 0)
			continue;

		$offsets[$index] = strlen(implode(PHP_EOL, $retval)) + 1; # +EOL

		$retval[] = _pdf_glue_object($object);
		}

	# cross-reference table

	$startxref = strlen(implode(PHP_EOL, $retval)) + 1; # +EOL
	$trailer = $objects[0]["dictionary"];

	if($optional)
		ksort($objects);

	$count = 0;
	$start = 0;

	$retval[] = sprintf("xref");

	foreach($objects as $index => $object)
		{
		if($count == 0)
			$start = $index;

		$count ++;

		if(isset($objects[$index + 1]))
			continue;

		$retval[] = sprintf("%d %d", $start, $count);

		foreach(range($start, $start + $count - 1) as $id)
			$retval[] = sprintf("%010d %05d %s", $offsets[$id], $objects[$id]["version"], $id == 0 ? "f" : "n");

		$count = 0;
		}

	# trailer

	$retval[] = "trailer";
	$retval[] = sprintf("<< %s >>", _pdf_glue_dictionary($trailer));

	$retval[] = "startxref";
	$retval[] = $startxref;

	$retval[] = "%%EOF";

	# final pdf file
	return(implode(PHP_EOL, $retval));
	}

################################################################################
# _pdf_glue_object ( array $object ) : string
# returns $object as string (obj-format).
################################################################################

function _pdf_glue_object($object)
	{
	$retval = [];

	$retval[] = sprintf("%d %d obj", $object["id"], $object["version"]);

		# apply dictionary
		if(isset($object["dictionary"]))
			$retval[] = sprintf("<< %s >>", _pdf_glue_dictionary($object["dictionary"]));

		# apply stream
		if(isset($object["stream"]))
			$retval[] = sprintf("stream\n%s\nendstream", $object["stream"]);

		# apply value
		if(isset($object["value"]))
			$retval[] = $object["value"];

	$retval[] = "endobj";

	return(implode(PHP_EOL, $retval));
	}

################################################################################
# _pdf_parse_array ( string $data ) : array
# returns array of found element as array and unparsed data as string.
################################################################################

function _pdf_parse_array($data)
	{
	$retval = [];

	while(1)
		{
		# check length
		if(! strlen($data))
			die(__FUNCTION__ . ": process runs out of data.");

		# check whitespaces
		elseif(in_array($data[0], ["\t", "\n", "\r", " "]))
			$data = substr($data, 1);

		# check string
		elseif($data[0] == "(")
			{
			$data = substr($data, 1);
			list($value, $data) = _pdf_parse_string($data);
			$data = substr($data, 1);

			$retval[] = sprintf("(%s)", $value);
			}

		# check name
		elseif($data[0] == "/")
			{
			$data = substr($data, 1);
			list($value, $data) = _pdf_parse_name($data);

			$retval[] = sprintf("/%s", $value);
			}

		# check dictionary
		elseif(substr($data, 0, 2) == "<<")
			{
			$data = substr($data, 2);
			list($value, $data) = _pdf_parse_dictionary($data);
			$data = substr($data, 2);

			$retval[] = sprintf("<< %s >>", _pdf_glue_dictionary($value));
			}

		# check hex
		elseif($data[0] == "<")
			{
			$data = substr($data, 1);
			list($value, $data) = _pdf_parse_hex($data);
			$data = substr($data, 1);

			$retval[] = sprintf("<%s>", $value);
			}

		# check array
		elseif($data[0] == "[")
			{
			$data = substr($data, 1);
			list($value, $data) = _pdf_parse_array($data);
			$data = substr($data, 1);

			$retval[] = sprintf("[%s]", _pdf_glue_array($value));
			}

		# check termination
		elseif($data[0] == "]")
			break;

		# check bool (false)
		elseif(substr($data, 0, 5) == "false")
			{
			$data = substr($data, 5);
			list($value, $data) = ["false", $data];

			$retval[] = $value;
			}

		# check bool (true)
		elseif(substr($data, 0, 4) == "true")
			{
			$data = substr($data, 4);
			list($value, $data) = ["true", $data];

			$retval[] = $value;
			}

		# check resource
		elseif(preg_match("/^(\d+ \d+ R)(.*)/is", $data, $matches) == 1)
			{
			list($null, $value, $data) = $matches;

			$retval[] = $value;
			}

		# must be numeric then
		else
			{
			list($value, $data) = _pdf_parse_numeric($data);

			$retval[] = $value;
			}
		}

	# return array and remaining data
	return([$retval, $data]);
	}

################################################################################
# _pdf_parse_comment ( string $data ) : array
# returns array of found element as string and unparsed data as string.
################################################################################

function _pdf_parse_comment($data)
	{
	$retval = "";

	while(1)
		{
		# check length
		if(! strlen($data))
			break;

		# check termination
		if(in_array($data[0], ["\n", "\r"]))
			break;

		# check escape
		if($data[0] == "\\")
			{
			$retval .= $data[0];

			$data = substr($data, 1);
			}

		# apply char
		$retval .= $data[0];

		# strip char
		$data = substr($data, 1);
		}

	# return comment and remaining data
	return([$retval, $data]);
	}

################################################################################
# _pdf_parse_dictionary ( string $data ) : array
# returns array of found element as array and unparsed data as string.
################################################################################

function _pdf_parse_dictionary($data)
	{
	$retval = [];

	$loop = 0;

	while(1)
		{
		if(! $data)
			break;
		elseif(in_array($data[0], ["\t", "\n", "\r", " "]))
			$data = substr($data, 1);
		elseif(substr($data, 0, 2) == ">>")
			break;
		else
			{
			$key = "";

			while(1)
				{
				# check length
				if(! strlen($data))
					die(__FUNCTION__ . ": process runs out of data (key).");

				# check whitespace
				elseif(in_array($data[0], ["\t", "\n", "\r", " "]))
					$data = substr($data, 1);

				# check invalid
				elseif(in_array($data[0], ["(", "<", "[", "f", "t"]))
					die(__FUNCTION__ . ": no other char than / allowed for key. data follows: " . $data);

				# check name
				elseif($data[0] == "/")
					{
					$data = substr($data, 1);
					list($value, $data) = _pdf_parse_name($data);

					$key = sprintf("/%s", $value);

					break;
					}
				else
					die(__FUNCTION__ . ": no other char than / allowed for key. data follows: " . $data);
				}

			$value = "";

			while(1)
				{
				# check length
				if(! strlen($data))
					die(__FUNCTION__ . ": process runs out of data (value).");

				# check whitespace
				elseif(in_array($data[0], ["\t", "\n", "\r", " "]))
					$data = substr($data, 1);

				# check string
				elseif($data[0] == "(")
					{
					$data = substr($data, 1);
					list($value, $data) = _pdf_parse_string($data);
					$data = substr($data, 1);

					$value = sprintf("(%s)", $value);

					break;
					}

				# check name
				elseif($data[0] == "/")
					{
					$data = substr($data, 1);
					list($value, $data) = _pdf_parse_name($data);

					$value = sprintf("/%s", $value);

					break;
					}

				# check dictionary
				elseif(substr($data, 0, 2) == "<<")
					{
					$data = substr($data, 2);
					list($value, $data) = _pdf_parse_dictionary($data);
					$data = substr($data, 2);

					$value = sprintf("<< %s >>", _pdf_glue_dictionary($value));

					break;
					}

				# check hex
				elseif($data[0] == "<")
					{
					$data = substr($data, 1);
					list($value, $data) = _pdf_parse_hex($data);
					$data = substr($data, 1);

					$value = sprintf("<%s>", $value);

					break;
					}

				# check array
				elseif($data[0] == "[")
					{
					$data = substr($data, 1);
					list($value, $data) = _pdf_parse_array($data);
					$data = substr($data, 1);

					$value = sprintf("[%s]", _pdf_glue_array($value));

					break;
					}

				# check bool (false)
				elseif(substr($data, 0, 5) == "false")
					{
					$data = substr($data, 5);
					list($value, $data) = ["false", $data];

					break;
					}

				# check bool (true)
				elseif(substr($data, 0, 4) == "true")
					{
					$data = substr($data, 4);
					list($value, $data) = ["true", $data];

					break;
					}

				# check resource
				elseif(preg_match("/^(\d+ \d+ R)(.*)/is", $data, $matches) == 1)
					{
					list($null, $value, $data) = $matches;

					break;
					}

				# must be numeric then
				else
					{
					list($value, $data) = _pdf_parse_numeric($data);

					break;
					}
				}

			$retval[$key] = $value;
			}

		$loop ++;

		# prevent loop forever
		if($loop > 1024)
			die(__FUNCTION__ . ": process stuck on data " . $data);
		}

	# return dictionary and remaining data
	return([$retval, $data]);
	}

################################################################################
# _pdf_parse_document ( string $data ) : array
# returns array of found element as array and unparsed data as warning.
################################################################################

function _pdf_parse_document($data)
	{
	$retval = [];

	if(preg_match("/^%PDF-(\d+)\.(\d+)[\s|\n]+(.*)[\s|\n]+startxref[\s|\n]+(\d+)[\s|\n]+%%EOF(.*)/is", $data, $matches) == 0)
		die(__FUNCTION__ . ": something is seriously wrong (invalid structure).");

	list($null, $major, $minor, $body, $startxref, $null) = $matches;

	################################################################################
	# pdf_parse_xref ( string $data ) : array
	# returns offsets from $data as string.
	# pdf got differentformats of xref
	################################################################################

	$offsets = [];

	$table = substr($data, $startxref);
	$mode = "";

	while(1)
		{
		if(! $table)
			break;
		elseif(in_array($table[0], ["\t", "\n", "\r", " "]))
			$table = substr($table, 1);
		elseif(preg_match("/^(\d+ \d+ obj)(.*)/is", $table, $matches) == 1)
			{
			list($value, $null) = _pdf_parse_object($table);

			$value["stream"] = PHP_EOL . wordwrap(bin2hex(gzuncompress($value["stream"])), 12, PHP_EOL, true);

			print_r($value); exit;
			}
		elseif(substr($table, 0, 5) == "%%EOF")
			break;
		elseif(substr($table, 0, 9) == "startxref")
			break;
		elseif(substr($table, 0, 7) == "trailer")
			{			
			$table = substr($table, 7);

			$mode = "trailer";
			}
		elseif(substr($table, 0, 4) == "xref")
			{
			$table = substr($table, 4);

			$mode = "first";
			}
		elseif($mode == "first")
			{
			list($first, $table) = _pdf_parse_numeric($table);

			$mode = "count";
			}
		elseif($mode == "count")
			{
			list($count, $table) = _pdf_parse_numeric($table);

			$mode = "offset";
			}
		elseif($mode == "offset")
			{
			list($offset, $table) = _pdf_parse_numeric($table);

			$mode = "generation";
			}
		elseif($mode == "generation")
			{
			list($generation, $table) = _pdf_parse_numeric($table);

			$mode = "used";
			}
		elseif($mode == "used")
			{
			list($used, $table) = _pdf_parse_name($table);

			if($used == "n")
				$offsets[$first] = $offset; # _pdf_parse_object($data)

			$count --;
			$first ++;

			if($count == 0)
				$mode = "first";
			else
				$mode = "offset";
			}
		elseif($mode == "trailer")
			{
			################################################################################
			# get objects by offset
			################################################################################

			foreach($offsets as $index => $offset_start)
				{
				$offset_stop = $startxref;

				foreach($offsets as $offset_test)
					{
					if($offset_test >= $offset_stop)
						continue;

					if($offset_test <= $offset_start)
						continue;

					$offset_stop = $offset_test;
					}

				$help = substr($data, $offset_start, $offset_stop - $offset_start - 1);

				list($value, $null) = _pdf_parse_object($help);

				if($value["id"] != $index)
					die(__FUNCTION__ . ": something is seriously wrong (invalid id).");

				$retval[$index]= $value;
				}

			################################################################################

			while(1)
				{
				if(! $table)
					break;
				elseif(in_array($table[0], ["\t", "\n", "\r", " "]))
					$table = substr($table, 1);
				elseif(substr($table, 0, 2) == "<<")
					{
					$table = substr($table, 2);

					list($trailer, $table) = _pdf_parse_dictionary($table);

					$table = substr($table, 2);

					break;
					}
				}

			$retval[0]["version"] = 65535;
			$retval[0]["dictionary"] = $trailer;

			if(isset($trailer["/Prev"]))
				$table = substr($data, $trailer["/Prev"]);
			else
				break;

#			$startxref = $trailer["/Prev"];
			}
		else
			{
			$pattern = [
				"\d+[\s|\n]+\d+[\s|\n]+obj[\s|\n]+.*?[\s|\n]+endobj",
				"xref[\s|\n]+.*",
				"trailer[\s|\n]+.*",
				"startxref[\s|\n]+\d+[\s|\n]+"
				];

			if(preg_match_all("/(" . implode("|", $pattern) . ")/is", $data, $matches) == 0)
				die(__FUNCTION__ . ": ...");

			foreach($matches[0] as $object)
				{
				if(substr($object, 0, 7) == "trailer")
					{
					# trailer is stored as null-object
					# version number is needed for future xref
					$retval[0]["version"] = 65535;

					$object = substr($object, 7);

					while(1)
						{
						if(! $object)
							break;
						elseif(in_array($object[0], ["\t", "\n", "\r", " "]))
							$object = substr($object, 1);
						elseif(substr($object, 0, 2) == "<<")
							{
							$object = substr($object, 2);

							list($retval[0]["dictionary"], $object) = _pdf_parse_dictionary($object);

							$object = substr($object, 2);

							break;
							}
						}

					continue;
					}

				if(substr($object, 0, 9) == "startxref")
					continue;

				list($k, $null) = _pdf_parse_object($object);

				$id = $k["id"];

				$retval[$id] = $k;
				}

			ksort($retval);

			return($retval);
			}
		}

	return($retval);
	}

################################################################################
# _pdf_parse_hex ( string $data ) : array
# returns array of found element as string and unparsed data as string.
################################################################################

function _pdf_parse_hex($data)
	{
	$retval = "";

	while(1)
		{
		# check length
		if(! strlen($data))
			die(__FUNCTION__ . ": process runs out of data.");

		# check termination
		if($data[0] == ">")
			break;

		# apply char
		$retval .= $data[0];

		# strip char
		$data = substr($data, 1);
		}

	# return hex and remaining data
	return([$retval, $data]);
	}

################################################################################
# _pdf_parse_name ( string $data ) : array
# returns array of found element as string and unparsed data as string.
################################################################################

function _pdf_parse_name($data)
	{
	$retval = "";

	while(1)
		{
		# check length
		if(! strlen($data))
			break;

		# check termination
		if(in_array($data[0], ["\t", "\n", "\r", " ", "(", "/", "<", ">", "[", "]"]))
			break;

		# apply char
		$retval .= $data[0];

		# strip char
		$data = substr($data, 1);
		}

	# return name and remaining data
	return([$retval, $data]);
	}

################################################################################
# _pdf_parse_numeric ( string $data ) : array
# returns array of found element as string and unparsed data as string.
################################################################################

function _pdf_parse_numeric($data)
	{
	$retval = "";

	while(1)
		{
		# check length
		if(! strlen($data))
			die(__FUNCTION__ . ": process runs out of data.");

		# check termination
		if(in_array($data[0], ["\t", "\n", "\r", " ", "(", "/", "<", ">", "[", "]", "f", "t"]))
			break;

		# apply char
		$retval .= $data[0];

		# strip char
		$data = substr($data, 1);
		}

	# return numeric and remaining data
	return([$retval, $data]);
	}

################################################################################
# _pdf_parse_object ( string $data ) : array
# returns array of found element as array and unparsed data as string.
################################################################################

function _pdf_parse_object($data)
	{
	$retval = [];

	if(preg_match("/^(\d+)[\s|\n]+(\d+)[\s|\n]+obj[\s|\n]*(.+)[\s|\n]*endobj.*/is", $data, $matches) == 0)
		die(__FUNCTION__ . ": something is seriously wrong.");

	list($null, $retval["id"], $retval["version"], $data) = $matches;

	# try to overcome this
	$data = ltrim($data);

 	if(substr($data, 0, 2) == "<<")
		{		
		$data = substr($data, 2);
		list($retval["dictionary"], $data) = _pdf_parse_dictionary($data);
		$data = substr($data, 2);

		# try to overcome this
		$data = ltrim($data);

		if(preg_match("/^stream[\s|\n]+(.+)[\s|\n]+endstream.*/is", $data, $matches) == 1) # !!! fails on hex streams sometimes
			list($null, $retval["stream"]) = $matches; # data for value
		}
	elseif(preg_match("/^stream[\s|\n]+(.+)[\s|\n]+endstream.*/is", $data, $matches) == 1) # !!! fails on hex streams sometimes
		list($null, $retval["stream"]) = $matches; # data for value
	else
		$retval["value"] = $data;

	# return object and nothing
	return([$retval, ""]);
	}

################################################################################
# _pdf_parse_string ( string $data ) : array
# returns array of found element as string and unparsed data as string.
################################################################################

function _pdf_parse_string($data)
	{
	$retval = "";

	while(1)
		{
		# check length
		if(! strlen($data))
			die(__FUNCTION__ . ": process runs out of data.");

		# check termination
		if($data[0] == ")")
			break;

		# check escape
		if($data[0] == "\\")
			{
			$retval .= $data[0];

			$data = substr($data, 1);
			}

		# apply char
		$retval .= $data[0];

		# strip char
		$data = substr($data, 1);
		}

	# return string and remaining data
	return([$retval, $data]);
	}
?>
