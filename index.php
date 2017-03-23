<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Chess board painting tool</title>
<script src='http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js' type='text/javascript'></script>
<script src='js/FileSaver.js' type='text/javascript'></script>

    <link href="http://netdna.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="dist/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="dist/css/bootstrap-colorpicker-plus.css" rel="stylesheet">
    
    <style type="text/css">
        .color-fill-icon{display:inline-block;width:16px;height:16px;border:1px solid #000;background-color:#fff;margin: 2px;}
        .dropdown-color-fill-icon{position:relative;float:left;margin-left:0;margin-right: 0}
		.well .markup{
			background: #fff;
			color: #777;
			position: relative;
			padding: 45px 15px 15px;
			margin: 15px 0 0 0;
			background-color: #fff;
			border-radius: 0 0 4px 4px;
			box-shadow: none;
		}

		.well .markup::after{
			content: "Example";
			position: absolute;
			top: 15px;
			left: 15px;
			font-size: 12px;
			font-weight: bold;
			color: #bbb;
			text-transform: uppercase;
			letter-spacing: 1px;
		}
    </style>
	
</head>

	<body>

 
	<script language='javascript'>

	var NBCOLS=8;
	var NBROWS=8;

	<?php 
	if (isset($_GET['c'])){
		echo "NBCOLS=".$_GET['c'].";";
	}
	if (isset($_GET['r'])){
		echo "NBROWS=".$_GET['r'].";";
	}
	
	$colPickers = array(
			array("id"=>"#col-white","var"=>"WHITE"),
			array("id"=>"#col-black","var"=>"BLACK"),
			array("id"=>"#col-background","var"=>"BORDER_COLOR"),
			array("id"=>"#col-text","var"=>"BORDER_TEXT_COLOR"),
			array("id"=>"#col-arrow-fill","var"=>"arrowStyle.fill"),
			array("id"=>"#col-arrow-stroke","var"=>"arrowStyle.stroke")
	);	
	?>

	var arrowStyle = {width:15,fill:"#ffffff",stroke:"#000000",lineWidth:2,rounded:true};
	
	var resources=[];

	class Pastille {
		constructor(resourcePath, clipx, clipy) {
		    this.x=0;
		    this.y=0;
		  }
		  setResource(resourcePath, clipx, clipy, clipw, cliph){
			    this.resourcePath=resourcePath;
			    this.clipx = clipx;
			    this.clipy = clipy;
			    this.clipw = clipw;
			    this.cliph = cliph;
			}
		  setPosition(x,y,w,h){ // in parent canvas coordinates
			  this.x = x;
			  this.y = y;
			  this.w = w;
			  this.h = h;  
			}
		  paint(ctx){
			  if (resources[this.resourcePath] != undefined){
				  ctx.drawImage(resources[this.resourcePath],this.clipx,this.clipy,this.clipw,this.cliph,this.x,this.y,this.w,this.h);
			  }else{
				  //console.log("Paint error: can't find resource for this path:"+this.resourcePath);
			  }
		  }	
		  unsetRes(){
			  this.resourcePath=undefined;
		  }	  
		  copyRes(pastille){
			    this.resourcePath = pastille.resourcePath;
			    this.clipx = pastille.clipx;
			    this.clipy = pastille.clipy;
			    this.clipw = pastille.clipw;
			    this.cliph = pastille.cliph;
			}
			dump(){
				return JSON.stringify(this);
			}
		}

	class Arrow {
		constructor(){
			this.points = [] ;
			this.style = arrowStyle ;
		}
		draw(ctx){
			//DrawArrow(ctx,this.start.x,this.start.y,this.end.x,this.end.y,this.style);
			DrawArrow3(ctx,this.points,this.style);
		}
	}
	
    class BoardLayer {
    	constructor(type,defaultVal){
        	this.type=type;
        	if (this.type == "GRID_LAYER"){
            	// grid of Pastille objects
        		this.items=[];
            	for (var row=0;row<NBROWS;row++){
            		this.items[row]=[] ;
            		for (var col=0;col<NBCOLS;col++){
            			var past=new Pastille();
        				past.setPosition(xOffset+col*CZ,yOffset+BOARDH-(row+1)*CZ,CZ,CZ);     
        				this.items[row][col]=past;   				
            		}
        		}
        	}else if(this.type=="ARROW_LAYER"){
            	// array of Arrow objects
            	this.items=[];            	
        	}
    	}
    	loadFromFile(obj){
        	if (obj.type == this.type){
            	switch(obj.type){
        			default:
	            		break;
        			case "ARROW_LAYER":
            			if (obj.items){
                			for(var i = 0 ; i < obj.items.length ; i++){
                				var arrow = new Arrow();
                				arrow.points = obj.items[i].points;
                				arrow.style = arrowStyle ; //obj.items[i].style;
                				this.items.push(arrow);
                			}                			
            			}        				
            			break;
        			case "GRID_LAYER": 
	                	for (var row=0;row<NBROWS;row++){
	                		for (var col=0;col<NBCOLS;col++){
		                		this.items[row][col].x = obj.items[row][col].x ; 
		                		this.items[row][col].y = obj.items[row][col].y ; 
		                		this.items[row][col].w = obj.items[row][col].w ; 
		                		this.items[row][col].h = obj.items[row][col].h ;
		                		if (obj.items[row][col].resourcePath){
			                		this.items[row][col].resourcePath = obj.items[row][col].resourcePath ;
			                		this.items[row][col].clipx = obj.items[row][col].clipx ;
			                		this.items[row][col].clipy = obj.items[row][col].clipy ;
			                		this.items[row][col].cliph = obj.items[row][col].cliph ;
			                		this.items[row][col].clipw = obj.items[row][col].clipw ;
		                		} 
		                	}
	                	}
	                	break;
				}
        	}        	
    	}
		clear(){
        	if (this.type == "GRID_LAYER"){
            	for (var row=0;row<NBROWS;row++){
					for (var col=0;col<NBCOLS;col++){
						this.items[row][col].unsetRes();
					}
            	}
			}
        	if (this.type == "ARROW_LAYER"){
        		this.items = [];
        	}
		}    
		paint(ctx){
        	if (this.type == "GRID_LAYER"){
				for (var row=0;row<NBROWS;row++){
					for (var col=0;col<NBCOLS;col++){
						this.items[row][col].paint(ctx);
					}
				}
			}
        	if (this.type == "ARROW_LAYER"){            	
            	for(var i = 0 ; i < this.items.length ; i++)
            		this.items[i].draw(ctx);
        		if (arrowPath.length > 0){
        			for (var i = 0 ; i < arrowPath.length ; i++){
            			var  p = cellCenter(arrowPath[i].row,arrowPath[i].col) ;
            			SpotPoint(ctx,p.x,p.y,"#000",START_CIRCLE_R);
        			}
        		}
            }
		}		  	
    }
    
		
	// border
	var xOffset = 50 ;
	var yOffset = 50 ;
	var BORDER_COLOR = "#000";
	var BORDER_TEXT_COLOR = "#fff";
	var BORDER_FONT_STYLE="20px Arial";
	var BLACK="#CF8948";
	var WHITE="#FFCC9C";
	var CZ=100;

	var BOARDH=NBROWS*CZ;
	var BOARDW=NBCOLS*CZ;
	var bDrawNotation=true;
	var bDrawPosNotation=false;
	var PIECES_SPRITES_PATHS = ["img/wikipedia.png"] ;
	var SPRITES_CZ=100;

	var START_CIRCLE_R = 20;

	function cellCenter(row,col){
		return {x:xOffset+(col+.5)*CZ,y:yOffset+BOARDH-(row+0.5)*CZ};
	}
	function repaintArrowBut(){
    	var ctx = $("#current-arrow")[0].getContext('2d');
    	var w=ctx.canvas.width;
    	var h=ctx.canvas.height;    	
    	var prct=0.40;
    	ctx.fillStyle=WHITE;
    	ctx.fillRect(0,0,w/2,h);
    	ctx.fillStyle=BLACK;
    	ctx.fillRect(w/2,0,w/2,h);
    	//DrawArrow(ctx,w/2*prct,h/2,w-w/2*prct,h/2,arrowStyle);
    	DrawArrow3(ctx,[{x:w/2*prct,y:h/2},{x:w-w/2*prct,y:h/2}],arrowStyle);
	}
	
	var brushPastille = new Pastille();
	brushPastille.setPosition(0,0,SPRITES_CZ,SPRITES_CZ);
    function setBrush(layer,path,clipx,clipy,clipw,cliph){
    	currentLayer = layer;
    	if(layer == "ARROWS_LAYER"){
	    	var ctx = $("#current-brush")[0].getContext('2d');
	    	ctx.canvas.width=SPRITES_CZ;
	    	ctx.canvas.height=SPRITES_CZ;
	    	var prct=0.20;
	    	DrawArrow(ctx,SPRITES_CZ*prct,SPRITES_CZ*(1-prct),SPRITES_CZ*(1-prct),SPRITES_CZ*prct,arrowStyle);
		}else{
	    	brushPastille.setResource(path, clipx, clipy, clipw, cliph);
	    	var ctx = $("#current-brush")[0].getContext('2d');
	    	ctx.canvas.width=clipw;
	    	ctx.canvas.height=cliph;
	    	brushPastille.paint(ctx);
    	}
	}
    
	var requestAnimationFrame = window.requestAnimationFrame || 
	    window.mozRequestAnimationFrame || 
	    window.webkitRequestAnimationFrame || 
	    window.msRequestAnimationFrame;

    //  hopefully get a valid cancelAnimationFrame function!                     
    var cancelRAF = window.cancelAnimationFrame || 
	    window.mozCancelAnimationFrame || 
	    window.webkitCancelAnimationFrame || 
	    window.msCancelAnimationFrame;


    //  store your requestAnimatFrame request ID value
    var requestId;


    var boardCnv = document.createElement('canvas');
    var displayZoom = 1;  
      
    function redrawBoard(){
    	
	    var ctx = boardCnv.getContext('2d');
	    
		ctx.fillStyle=BORDER_COLOR;
		ctx.fillRect(0,0,boardCnv.width,boardCnv.height);

		if (bDrawNotation){
			ctx.textAlign="center";
			ctx.textBaseline = 'middle';
			ctx.font=BORDER_FONT_STYLE;
			ctx.fillStyle=BORDER_TEXT_COLOR;
			for (var row=0;row<NBROWS;row++){
				ctx.fillText(row+1,xOffset/2,yOffset+BOARDH-(row+.5)*CZ);
				ctx.fillText(row+1,1.5*xOffset+BOARDW,yOffset+BOARDH-(row+.5)*CZ);
			}
			for (var col=0;col<NBCOLS;col++){
				var colLabel = String.fromCharCode(97 + col);
				ctx.fillText(colLabel,xOffset+(col+.5)*CZ,1.5*yOffset+BOARDH);
				ctx.fillText(colLabel,xOffset+(col+.5)*CZ,.5*yOffset);
			}
		}
		


				
		// board cells background
		for (var row=0;row<NBROWS;row++){
			for (var col=0;col<NBCOLS;col++){
				ctx.fillStyle=(row+col)%2?WHITE:BLACK;
				ctx.fillRect(xOffset+col*CZ,yOffset+BOARDH-(row+1)*CZ,CZ,CZ);
			}
		}

		// notations
		if (bDrawPosNotation){
			ctx.font="20px Georgia";
			ctx.textAlign="center";
			var pos = 0 ;
			for (var row=0;row<NBROWS;row++){
				for (var col=0;col<NBCOLS;col++){
					ctx.fillStyle=(row+col+1)%2?WHITE:BLACK;
					ctx.fillText(pos,xOffset+(col+.5)*CZ,yOffset+BOARDH-(row+.5)*CZ);
					pos ++ ;
				}
			}
		}

		// cells states
		/*for (var row=0;row<NBROWS;row++){
			for (var col=0;col<NBCOLS;col++){
				switch(boardCellStates[row][col]){
					default: // "NONE"
						break;
					case "SELECTED":
						break;
					case "CANCEL":
						break;
				}
			}
		}*/
		
		boardLayers["ARROWS_LAYER"].paint(ctx);
		boardLayers["PIECES_LAYER"].paint(ctx);
		boardLayers["MOVES_LAYER"].paint(ctx);
		boardLayers["CELLS_LAYER"].paint(ctx);
								

		// paint from memory to screen 
		var canvDisp = $("#board-canvas")[0];
		var ctxDisp=canvDisp.getContext('2d'); 
		ctxDisp.drawImage(boardCnv,0,0,boardCnv.width,boardCnv.height,0,0,canvDisp.width,canvDisp.height);
		
		
		
	}



    function SpotPoint(ctx,x,y,color,r){
        if (r === undefined) r = 5 ;
        ctx.fillStyle=color;
		ctx.beginPath();
        ctx.arc(x,y,r,0,Math.PI*2);
        ctx.fill();
    }
    
	function DrawArrow3(ctx,points,style){
	    
		var w = style.width;        
	    
	    ctx.fillStyle = style.fill;
	    ctx.strokeStyle = style.stroke;
	
	    var bRounded = style.rounded;
	    var bEndArrow = true ;
		var nbPoints = points.length ;
		
	    ctx.lineCap = "round"; //"square";
	    ctx.lineJoin = "round";
		// outside
	    ctx.beginPath();
	    ctx.lineWidth = style.width+style.lineWidth;
		ctx.moveTo(points[0].x, points[0].y);
		for (var idx = 1 ; idx < nbPoints ; idx++){
			ctx.lineTo(points[idx].x, points[idx].y);
		}
		ctx.stroke();

		// inside
		ctx.beginPath();
	    ctx.lineWidth = style.width-style.lineWidth;
	    ctx.strokeStyle = style.fill;
		ctx.moveTo(points[0].x, points[0].y);
		for (var idx = 1 ; idx < nbPoints ; idx++){
			ctx.lineTo(points[idx].x, points[idx].y);
		}
		ctx.stroke();
		
		
		if (bEndArrow) {
			// considere last segment
			var xs = points[nbPoints-2].x;
			var ys = points[nbPoints-2].y;
			var xe = points[nbPoints-1].x;
			var ye = points[nbPoints-1].y;
			var arrowW = w ;
			// segment dy/dx
			var alpha = (xe == xs) ? (ye > ys ? Math.PI / 2 : 3 * Math.PI / 2) : Math.atan2(ye-ys,xe-xs)  ;
			
			ctx.lineWidth = 2*style.lineWidth;
			ctx.strokeStyle = style.stroke;
			ctx.fillStyle = style.fill ;
		    ctx.lineCap = "butt";
		    ctx.lineJoin = "round";
			ctx.beginPath();
			ctx.moveTo(xe-w/2*Math.sin(alpha), ye+w/2*Math.cos(alpha));
			ctx.lineTo(xe-arrowW*Math.sin(alpha),ye+arrowW*Math.cos(alpha));
			ctx.lineTo(xe+arrowW*Math.cos(alpha),ye+arrowW*Math.sin(alpha));
			ctx.lineTo(xe+arrowW*Math.sin(alpha),ye-arrowW*Math.cos(alpha));
			ctx.lineTo(xe+w/2*Math.sin(alpha),ye-w/2*Math.cos(alpha));
			ctx.stroke();
			ctx.fill();
		}		
	}

    
    function DrawArrow(ctx,xs,ys,xe,ye,style){

        var w = style.width;        
        
        ctx.fillStyle = style.fill;
        ctx.strokeStyle = style.stroke;
        ctx.lineWidth = style.lineWidth;

        var bRounded = style.rounded;
        var bEndArrow = true ;

        var arrowW = w ;
        
		ctx.beginPath();

		// segment dy/dx
		var alpha = (xe == xs) ? (ye > ys ? Math.PI / 2 : 3 * Math.PI / 2) : Math.atan2(ye-ys,xe-xs)  ;
		//console.log("alpha = ",alpha, " = ", 360 * alpha / (2*Math.PI));
		  
		
		var a = {x:xs+w/2*Math.sin(alpha), y:ys-w/2*Math.cos(alpha)};
		var b = {x:xs-w/2*Math.sin(alpha), y:ys+w/2*Math.cos(alpha)};
		var c = {x:xe-w/2*Math.sin(alpha), y:ye+w/2*Math.cos(alpha)};
		var d = {x:xe+w/2*Math.sin(alpha), y:ye-w/2*Math.cos(alpha)};
				
		ctx.moveTo(a.x,a.y);
		if (bRounded)
			ctx.arc(xs,ys,w/2,3*Math.PI/2+alpha,Math.PI/2+alpha,true);
		else
			ctx.lineTo(b.x,b.y);

		ctx.lineTo(c.x,c.y);
		if (bEndArrow) {
			ctx.lineTo(xe-arrowW*Math.sin(alpha),ye+arrowW*Math.cos(alpha));
			ctx.lineTo(xe+arrowW*Math.cos(alpha),ye+arrowW*Math.sin(alpha));
			ctx.lineTo(xe+arrowW*Math.sin(alpha),ye-arrowW*Math.cos(alpha));
			ctx.lineTo(d.x,d.y);
		}
		else if (bRounded)
			ctx.arc(xe,ye,w/2,Math.PI/2+alpha,3*Math.PI/2+alpha,true);
		else
			ctx.lineTo(d.x,d.y);
		
        //ctx.lineTo(xs+w/2,ys);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        
        //debug
        /*SpotPoint(ctx,a.x,a.y,"#f00");
        SpotPoint(ctx,b.x,b.y,"#0f0");
        SpotPoint(ctx,c.x,c.y,"#00f");
        SpotPoint(ctx,d.x,d.y,"#fff");*/
        

    }

    // IO functions 
    function saveImage(){
		var fn = $("#image-file-name").val()+".png";
    	//var canvas = document.getElementById("board-canvas");
		boardCnv.toBlob(function(blob) {
     		saveAs(blob, fn);
 		});
    }
    
    // layers        
    var boardLayers=[];    
    var lastClickedCell = null ;
    var arrowPath = []; // clicked cells for arrow drawing 
    // edition mode
    var currentLayer="PIECES_LAYER" ;

    var fileEvent = null ; // to store the file event when inputfile has changed, upload will be fired by "load" menu
    function updateFileEvent(evt){
    	fileEvent = evt ;
    	loadBoard();
	}
                                   
	function initLayers(){
		boardLayers["PIECES_LAYER"]=new BoardLayer("GRID_LAYER");
		boardLayers["MOVES_LAYER"]=new BoardLayer("GRID_LAYER");
		boardLayers["CELLS_LAYER"]=new BoardLayer("GRID_LAYER");
		boardLayers["ARROWS_LAYER"]=new BoardLayer("ARROW_LAYER");
	}
	function initCanvas(){
		boardCnv.width=BOARDW+2*xOffset;
		boardCnv.height=BOARDH+2*yOffset;

		var canv = $("#board-canvas")[0];
		canv.width=displayZoom*boardCnv.width;
		canv.height=displayZoom*boardCnv.height;
		$("#board-canvas").unbind( "click" );
		$("#board-canvas").click(function(e){
			var o = {offsetX : e.offsetX / displayZoom , offsetY : e.offsetY / displayZoom } ;  

			if (o.offsetX >= xOffset && o.offsetX < (xOffset+BOARDW) && o.offsetY >= yOffset && o.offsetY < (yOffset+BOARDH)){
				var col = Math.ceil((o.offsetX-xOffset)/CZ);
				var row = NBROWS+1-Math.ceil((o.offsetY-yOffset)/CZ);

				console.log("col=",col,",row=",row);
				
				// pastille indexes start at 0
				col--;
				row--;

				PaintCellWithBrush(row,col,e);
								
				lastClickedCell = {col:col,row:row};
				
			}
		});
	}

	function loadResources(){

		var pathIdx = 0 ;
		
		var idx = 0 ;
		
		function loadPiecesSet(){
			var sprites=new Image();
			var path = PIECES_SPRITES_PATHS[pathIdx];
			sprites.src = path;
			sprites.onload=function() {				
				resources[path]=this;
				var nbCols = sprites.width / SPRITES_CZ;
				for (var i = 0 ; i < nbCols ; i++){
					for (var j = 0 ; j < 2 ; j++){
						var newCanvas = 
						    $('<canvas/>',{'class':'piece-cnv'});
						var cnv=newCanvas[0];
						cnv.width=SPRITES_CZ/2;
						cnv.height=SPRITES_CZ/2;
						var ctx=cnv.getContext('2d'); 
						ctx.drawImage(this,i*SPRITES_CZ,j*SPRITES_CZ,SPRITES_CZ,SPRITES_CZ,0,0,SPRITES_CZ/2,SPRITES_CZ/2);
						$("#pieces").append(newCanvas);
						(function(i,j){
							newCanvas.click(function(e){
								setBrush("PIECES_LAYER",path,i*SPRITES_CZ,j*SPRITES_CZ,SPRITES_CZ,SPRITES_CZ);					
							});
						})(i,j);
						switch(j){
							default:
								newCanvas.addClass('white-piece');
							break;
							case 1:
								newCanvas.addClass('black-piece');
							break;
						}
						newCanvas.attr("id","piece"+idx);
						idx++;
					}
				}
				$("#piece0").trigger("click");
				if (pathIdx<PIECES_SPRITES_PATHS.length){
					pathIdx++;
					loadPiecesSet();
				}
	        };
		}

	        

        function loadMove(path){
            var img=new Image();
            img.src=path;
            img.onload=function(){
    			resources[path]=this;
            	var newCanvas = 
				    $('<canvas/>',{'class':'move-cnv'});
				var cnv=newCanvas[0];
				cnv.width=SPRITES_CZ/2;
				cnv.height=SPRITES_CZ/2;
				var ctx=cnv.getContext('2d'); 
				ctx.fillStyle="#cf0";
				ctx.fillRect(0,0,SPRITES_CZ,SPRITES_CZ);
				ctx.drawImage(this,0,0,SPRITES_CZ/2,SPRITES_CZ/2);
				$("#moves").append(newCanvas);            
				newCanvas.click(function(e){
					setBrush("MOVES_LAYER",path,0,0,SPRITES_CZ,SPRITES_CZ);					
				});
			}
        }

        loadPiecesSet();
		loadMove("img/jump.png");
		loadMove("img/moveok.png");
		loadMove("img/cross.png");
		loadMove("img/triangle.png");	
	}

	function initRowsCols(){
    	$("#conf-nb-rows").val(NBROWS);
		$("#conf-nb-cols").val(NBCOLS);
	}
	                    	
	function reinitBoard(bAskConfirm){
		var r = parseInt($("#conf-nb-rows").val());
		var c = parseInt($("#conf-nb-cols").val());

		if (isNaN(r) || isNaN(c)){
			alert("Please enter numbers");
		}else{
			var ok = true ;
			if (bAskConfirm){
				ok = confirm("Create a "+r+"x"+c+" board.\r\nThis will reset all board data. Do we proceed?");
			}
			if (ok){
				NBROWS = r ;
				NBCOLS = c ;
				BOARDH=NBROWS*CZ ;
				BOARDW=NBCOLS*CZ ;
	
				initLayers();
		        initRowsCols()
		        initCanvas();
				redrawBoard();		        
			}
		}
	}
	                     	
	
    function init(){
    	document.getElementById('fileinput').addEventListener('change', updateFileEvent, false);

        initLayers();
        initRowsCols()

    	// Check for the various File API support.
    	var bFileAPIok = true;
    	if (window.File && window.FileReader && window.FileList && window.Blob) {
    	  // Great success! All the File APIs are supported.
    	} else {
    		bFileAPIok = false ;
    	}

        initCanvas();

        loadResources();
	
		redrawBoard();
        

		$("#current-brush").width(SPRITES_CZ).height(SPRITES_CZ);

		$("#current-arrow").click(function(e){
			setBrush("ARROWS_LAYER");					
		});
		// paint arrow button
		repaintArrowBut();

		$("#piece0").trigger("click");

		$.each([
				{value:.3,text:"30%"},
				{value:.4,text:"40%"},
				{value:.5,text:"50%"},
				{value:.6,text:"60%"},
				{value:.7,text:"70%"},
				{value:.8,text:"80%"},
				{value:.9,text:"90%"},
				{value:1,text:"100%"}
				], function (i, item) {
		    $('#sel-zoom').append($('<option>', { 
		        value: item.value,
		        text : item.text 
		    }));
		});
		$('#sel-zoom').val(1);
		$('#sel-zoom').on("change",function(){
			displayZoom = $('#sel-zoom').val();
	        initCanvas();
			redrawBoard();	
		});
 	}

 	
	function clearBoard(){
		boardLayers["PIECES_LAYER"].clear();
		boardLayers["CELLS_LAYER"].clear();
		boardLayers["MOVES_LAYER"].clear();
		boardLayers["ARROWS_LAYER"].clear();
		redrawBoard();
	}

	function clearMoves(){
		boardLayers["MOVES_LAYER"].clear();
		redrawBoard();
	}
	function clearPieces(){
		boardLayers["PIECES_LAYER"].clear();
		redrawBoard();
	}
	function clearCells(){
		boardLayers["CELLS_LAYER"].clear();
		redrawBoard();
	}
	function clearArrows(){
		boardLayers["ARROWS_LAYER"].clear();
		redrawBoard();	
	}
	
	function classicBoard(){
		
	}
	function showAll(){
		$(".white-piece").show();
		$(".black-piece").show();
	}
	function showWhites(){
		$(".white-piece").show();
		$(".black-piece").hide();
	}
	function showBlacks(){
		$(".white-piece").hide();
		$(".black-piece").show();
	}

	function updateColPickers(){
		<?php foreach($colPickers as $item) { ?> 
			$('<?php echo $item["id"];?> .color-fill-icon').css('background-color', <?php echo $item["var"];?>);
		<?php } ?>
	}
	
	function saveBoard(){
		//console.log(JSON.stringify(boardLayers));
		var fn = $("#save-file-name").val()+".txt";
		var backup = { 
				config : {cols: NBCOLS, rows: NBROWS, white : WHITE , black : BLACK , border : BORDER_COLOR , bordertxt : BORDER_TEXT_COLOR , arrowFill : arrowStyle.fill , arrowStroke : arrowStyle.stroke } ,
				pieces : boardLayers["PIECES_LAYER"] ,
				moves : boardLayers["MOVES_LAYER"] ,
				arrows : boardLayers["ARROWS_LAYER"] ,
				cells : boardLayers["CELLS_LAYER"] ,
		};
		var blob = new Blob([JSON.stringify(backup)], {type: "text/plain;charset=utf-8"});
		saveAs(blob, fn);
	}
	function loadBoard(){
		if (fileEvent == null){
			alert("No file selected");
			return;
		}
		// Check for the various File API support.
		if (window.File && window.FileReader && window.FileList && window.Blob) {
			//Retrieve the first (and only!) File from the FileList object
			var f = fileEvent.target.files[0]; 
			if (f) {
				var r = new FileReader();
				r.onload = function(e) { 
					var contents = e.target.result;					
			        /*alert( "Got the file.n" 
			              +"name: " + f.name + "n"
			              +"type: " + f.type + "n"
			              +"size: " + f.size + " bytesn"
			              + "starts with: " + contents.substr(10, contents.indexOf("n"))
			        );*/  
					var obj = JSON.parse(contents);
					// check dimensions of the board
					if (obj.config){
						if ((obj.config.cols) && (obj.config.rows)){
							if ((obj.config.cols != NBCOLS) || (obj.config.rows != NBROWS)){
						    	$("#conf-nb-rows").val(obj.config.rows);
								$("#conf-nb-cols").val(obj.config.cols);
								reinitBoard(false);
							}
						} 
					}


					clearBoard();
			        if (obj.pieces) boardLayers["PIECES_LAYER"].loadFromFile(obj.pieces);
			        if (obj.moves) boardLayers["MOVES_LAYER"].loadFromFile(obj.moves);
			        if (obj.cells) boardLayers["CELLS_LAYER"].loadFromFile(obj.cells);
			        if (obj.config){
			        	NBCOLS = obj.config.cols ;
				        NBROWS = obj.config.rows ;
			        	WHITE = obj.config.white;
			        	BLACK = obj.config.black;
			        	BORDER_COLOR = obj.config.border;
			        	BORDER_TEXT_COLOR = obj.config.bordertxt ;
			        	arrowStyle.fill = obj.config.arrowFill ; 
			        	arrowStyle.stroke = obj.config.arrowStroke ; 				        
			        }	        
			        // must be after config: arrows are affected by arrowStyle
			        if (obj.arrows) boardLayers["ARROWS_LAYER"].loadFromFile(obj.arrows);
			        redrawBoard();
			        repaintArrowBut();
        			if (currentLayer == "ARROWS_LAYER"){
        				setBrush("ARROWS_LAYER");
        			}
			        updateColPickers();
					console.log(obj);
			    }
		        r.readAsText(f);
		    } else { 
		      alert("Failed to load file");
		    }		
		} else {
		  alert('The File APIs are not fully supported by your browser.');
		}
	}

	function reverse(){
		
	}

	function getSegmentAngle(xs,ys,xe,ye){
		var alpha = (xe == xs) ? (ye > ys ? Math.PI / 2 : 3 * Math.PI / 2) : Math.atan2(ye-ys,xe-xs)  ;
		return alpha;
	}


	function PaintCellWithBrush(row,col,e){
		console.log("currentLayer:",currentLayer);			
		if (currentLayer == "ARROWS_LAYER"){
			if ((arrowPath.length == 0) || e.ctrlKey){
				arrowPath.push({row:row,col:col});
			}else{
				if ((arrowPath[0].row != row) || (arrowPath[0].col != col)){
					arrowPath.push({row:row,col:col});
					// create an arrow
					var arrow = new Arrow();
					for (var i = 0 ; i < arrowPath.length; i++){
						var p = cellCenter(arrowPath[i].row,arrowPath[i].col);
						if (i == 0){
							var p2 = cellCenter(arrowPath[i+1].row,arrowPath[i+1].col);
							var alpha = getSegmentAngle(p.x,p.y,p2.x,p2.y);
							p.x +=  2*START_CIRCLE_R * Math.cos(alpha);
							p.y +=  2*START_CIRCLE_R * Math.sin(alpha);
						}
						if (i == (arrowPath.length-1)){
							var p2 = cellCenter(arrowPath[i-1].row,arrowPath[i-1].col);
							var alpha = getSegmentAngle(p.x,p.y,p2.x,p2.y);
							p.x +=  2*START_CIRCLE_R * Math.cos(alpha);
							p.y +=  2*START_CIRCLE_R * Math.sin(alpha);
						}
						arrow.points.push(p);
					}
					boardLayers[currentLayer].items.push(arrow);
				}				
				arrowPath = [] ;
			}
			redrawBoard();
		}else if (brushPastille.resourcePath != undefined){
			var cells=boardLayers[currentLayer].items;
			
			if (cells[row][col].resourcePath == brushPastille.resourcePath && cells[row][col].clipx == brushPastille.clipx  && cells[row][col].clipy == brushPastille.clipy){
				cells[row][col].unsetRes();
			}
			else
			{
				cells[row][col].copyRes(brushPastille);
			}
			// ctrl?
			if (e.ctrlKey){
				// add horizontal symetric
				cells[row][NBCOLS-1-col].copyRes(brushPastille);
			}
			// shift?
			if (e.shiftKey){
				if (lastClickedCell != null){
					if ((col != lastClickedCell.col) || (row != lastClickedCell.row)){
						// try to find vert, horz or diag paths
						var dc = col - lastClickedCell.col ;  
						var dr = row - lastClickedCell.row ;
						if ((Math.abs(dc) == Math.abs(dr)) || (row == lastClickedCell.row) || (col == lastClickedCell.col)){
							var stepr = dr == 0 ? 0 : dr / Math.abs(dr)  ;
							var stepc = dc == 0 ? 0 : dc / Math.abs(dc)  ;
							var nb = Math.max(Math.abs(dc),Math.abs(dr));
							for (var i = 1 ; i <= nb ; i++){
								cells[row-i*stepr][col-i*stepc].copyRes(brushPastille);
							}																	
						}   
					} 
				}
			}
			redrawBoard();
		}
	}	

	function incZoom(){
		displayZoom = Math.min(1,displayZoom+.1);
        initCanvas();
		redrawBoard();		        
	}
	function decZoom(){
		displayZoom = Math.max(0,displayZoom-.1);
        initCanvas();
		redrawBoard();		        
	}
	
	window.onload=init;
	</script>
	<style>
	.container {
		width : 1185px;
		margin : 0 auto ;
	}
	p{
	margin : 0 ;
	}
	#cnv {
		float:left;
	}
	.piece-cnv {
		background : #fc0 ;
	}
	.move-cnv {
		background : #cf0 ;
	}
	.arrow-cnv {
		background : #ddd ;
	}
	.piece-cnv , .move-cnv , .arrow-cnv {
		border-radius: 4px;
		margin: 2px;
		cursor: pointer ;
	}
	#sidebar{
	    width: 250px;
	    float: left;
	    height: 900px;
	    overflow: auto;
	}
	.seperator{
		width : 100%;
		height : 3px;
		background-color: #ddd ;
	}
	#selection{
		text-align : center ;
	}
	</style>

	<div class="container">
	<div id="cnv">
		<canvas id="board-canvas"></canvas>
	</div>
	
	<div id="sidebar">
		<div id="tools">
			<p>Zoom: <select id="sel-zoom">
			</select>
			</p>
			<p>Board setup: <br>
			<input type="text" size="2" id="conf-nb-rows"> x <input size="2" type="text" id="conf-nb-cols"> : <a href="javascript:reinitBoard(true)">apply</a><br>
			<input type="text" id="save-file-name" value="myboard">.txt <a href="javascript:saveBoard()">Save</a>
			 Load:
			<input type="file" id="fileinput" /></p>
			<div class="seperator"></div>
			<p>Board image: <br><input type="text" id="image-file-name" value="myboard">.png <a href="javascript:saveImage()">Save</a> </p>
			<div class="seperator"></div>
			<p>Clear: <a href="javascript:clearBoard()">all</a> <a href="javascript:clearPieces()">pieces</a> <a href="javascript:clearMoves()">moves</a> <a href="javascript:clearCells()">cells</a>  <a href="javascript:clearArrows()">arrows</a>  
		</div>
			<div class="seperator"></div>
		<div id="selection">
			<p>Current brush</p>
			<canvas id="current-brush" width="100" height="100"></canvas>
		</div>
					<div class="seperator"></div>
		<div id="colors">
			<p>board colors</p>
			<div class="btn-group btn-group-sm">
		      <button type="button" class="btn btn-default" id="col-white"><span class="color-fill-icon dropdown-color-fill-icon" style="background-color:#000;"></span>&nbsp;<b class="caret"></b></button>
			  <button type="button" class="btn btn-default" id="col-black"><span class="color-fill-icon dropdown-color-fill-icon" style="background-color:#000;"></span>&nbsp;<b class="caret"></b></button>
		      <button type="button" class="btn btn-default" id="col-background"><span class="color-fill-icon dropdown-color-fill-icon" style="background-color:#000;"></span>&nbsp;<b class="caret"></b></button>
			  <button type="button" class="btn btn-default" id="col-text"><span class="color-fill-icon dropdown-color-fill-icon" style="background-color:#000;"></span>&nbsp;<b class="caret"></b></button>
			</div>
		</div>
		<div id="moves">
			<p>moves</p>
		</div>
		<div id="arrows">
			<p>arrows</p>
			<div class="btn-group btn-group-sm">
		      	<button type="button" class="btn btn-default" id="col-arrow-stroke"><span class="color-fill-icon dropdown-color-fill-icon" style="background-color:#000;"></span>&nbsp;<b class="caret"></b></button>
				<button type="button" class="btn btn-default" id="col-arrow-fill"><span class="color-fill-icon dropdown-color-fill-icon" style="background-color:#000;"></span>&nbsp;<b class="caret"></b></button>
		    </div>
			<canvas id="current-arrow" class="arrow-cnv" width="100" height="50"></canvas>
		    <script src="http://netdna.bootstrapcdn.com/bootstrap/3.3.2/js/bootstrap.min.js"></script>
		    <script src="dist/js/bootstrap-colorpicker.min.js"></script>
		    <script src="dist/js/bootstrap-colorpicker-plus.js"></script>
		    <script type="text/javascript">
		    $(function(){
		        <?php
		        foreach($colPickers as $item){ ?>
		        	var cp = $('<?php echo $item["id"];?>');
		        	cp.colorpickerplus();
		        	$('.color-fill-icon', cp).css('background-color', <?php echo $item["var"];?>);
		        	cp.on('changeColor', function(e,color){
		        		if(color==null) {
		        			//when select transparent color
		        			$('.color-fill-icon', $(this)).addClass('colorpicker-color');
		        		} else {
		        			$('.color-fill-icon', $(this)).removeClass('colorpicker-color');
		        			$('.color-fill-icon', $(this)).css('background-color', color);
		        			<?php echo $item["var"];?>=color;
		        			redrawBoard();
		        			repaintArrowBut();
		        			if (currentLayer == "ARROWS_LAYER"){
		        				setBrush("ARROWS_LAYER");
		        			}
		        		}
		        	});
		        <?php } ?>
			});
			</script>
     		</div>
		<div id="pieces">
			<p>pieces: <a href="javascript:showAll()">all</a>, <a href="javascript:showWhites()">whites</a>, <a href="javascript:showBlacks()">blacks</a></p>
			<div id="pieces-container">
			</div>
		</div>
	</div>
	</div>
</body>
</html>
