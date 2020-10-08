// Trumowyg
import icons from "trumbowyg/dist/ui/icons.svg";
import "trumbowyg/dist/ui/trumbowyg.css"
import "trumbowyg/dist/trumbowyg.js";
$.trumbowyg.svgPath = icons;

// Codemirror
import CodeMirror from 'codemirror/lib/codemirror.js';
window.CodeMirror = CodeMirror;
import 'codemirror/lib/codemirror.css';
import 'codemirror/addon/mode/multiplex.js';
import 'codemirror/addon/fold/xml-fold.js';
import 'codemirror/addon/edit/closebrackets.js';
import 'codemirror/addon/edit/matchtags.js';
import 'codemirror/addon/edit/closetag.js';
import 'codemirror/addon/selection/active-line.js';
import 'codemirror/addon/comment/continuecomment.js';
import 'codemirror/addon/search/search.js';
import 'codemirror/addon/search/searchcursor.js';
import 'codemirror/addon/search/jump-to-line.js';
import 'codemirror/addon/dialog/dialog.js';
import 'codemirror/addon/dialog/dialog.css';

import 'codemirror/mode/htmlmixed/htmlmixed.js';
import 'codemirror/mode/xml/xml.js';
import 'codemirror/mode/javascript/javascript.js';
import 'codemirror/mode/css/css.js';
import 'codemirror/mode/twig/twig.js';