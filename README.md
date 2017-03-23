# Board Painter 

This tool was originaly made to create chess board illustrations for [Musketeer Chess](http://musketeerchess.net) (rules, board setup, pieces moves and captures...).

## Demo

You can test it on [Musketeer Chess site] with many more pieces (this code on only includes classic chess pieces)

## installation

Upload the source code on a web server with PHP. 

## Uses

- [Bootstrap colorpicker](https://github.com/itsjavi/bootstrap-colorpicker)
- jQuery
- [FileSaver.js](https://github.com/eligrey/FileSaver.js)

## User guide 

A lot of settings are available to change the board configuration and items’ colours :

- Zoom : display zoom of the board preview
- Board setup : cols x rows
- Save : choose file name to save current settings on your computer
- Load : load settings from your computer
- Board image : file name of the board image to be saved (PNG file)
- Save : save image
- Clear : you can clear all, or just pieces, moves or arrows
- Current brush : displays the current brush. Change it by clicking on an item (move/piece/arrow)
- Board colour : choose the 2 colours of the board+ border (background and text)
- Moves : brush patterns for moves layer
- Arrows : brush pattern for arrows + colour options (fill and borders)
- Pieces : brush patterns for pieces. Links “all”, “white” and “blacks” are filters.

### Tricks 

- Click on a square to replace pattern, or erase it if same as brush
- Shift+Click : fills a line with current pattern (horizontal, vertical or diagonal)
- Ctrl+Click : paints current pattern + same pattern at symetric position (for instance to place 2 knights at their start position with one single click)

## Special thanks

Special thanks to Dr Zied Haddad from Musketeer Chess who allowed me to share this code.