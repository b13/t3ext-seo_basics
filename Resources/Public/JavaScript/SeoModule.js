/***************************************************************
 *  Copyright notice - MIT License (MIT)
 *
 *  (c) 2007-2014 Benni Mack <benni@typo3.org>
 *  All rights reserved
 *
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is
 *  furnished to do so, subject to the following conditions:
 *
 *  The above copyright notice and this permission notice shall be included in
 *  all copies or substantial portions of the Software.
 *
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 ***************************************************************/
/**
 * simple JavaScript functionality for highlighting text fields
 */
;(function($) {
	var seoColor = {
		yellow: "#ffff70",
		red: "#ff8040",
		green: "#c0ff70",
		white: "#ffffff"
	};

	var initialize = function() {
		initializeEvents();
		$(".seoTitleTag").each(function() {
			checkTitleTag($(this));
		});
		$(".seoKeywords").each(function() {
			checkKeywords($(this));
		});
		$(".seoDescription").each(function() {
			checkDescription($(this));
		});
	};

	var initializeEvents = function() {
		$(document).on("keyup keypress", ".seoTitleTag", function() {
			checkTitleTag($(this));
		}).on("keyup keypress", ".seoKeywords", function() {
			checkKeywords($(this));
		}).on("keyup keypress", ".seoDescription", function() {
			checkDescription($(this));
		});
	};

	var checkTitleTag = function($formField) {
		var size = $formField.val().length;
		var color = "green";
		if (size > 65) { color = "red"; }
		if (size < 50) { color = "yellow"; }
		if (size == 0) { color = "white"; }
		$formField.css({backgroundColor: seoColor[color]});
	};

	var checkKeywords = function($formField) {
		var numKeywords = 0;
		var val = $formField.val();
		if (val.length) {
			numKeywords = 1;
			var keywords = val.match(/,/gi);
			if (keywords) { numKeywords = keywords.length+1; }
		}
		var color = "green";
		if (numKeywords > 6) { color = "red"; }
		if (numKeywords < 2)  { color = "yellow"; }
		if (numKeywords < 1)  { color = "white"; }
		$formField.css({backgroundColor: seoColor[color]});
	};

	var checkDescription = function($formField) {
		var size = $formField.val().length;
		var color = "green";
		if (size > 150)  { color = "red"; }
		if (size < 115)  { color = "yellow"; }
		if (size ==  0)  { color = "white"; }
		$formField.css({backgroundColor: seoColor[color]});
	};


	$(document).ready(function() {
		initialize();
	});

})(TYPO3.jQuery);
