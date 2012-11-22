/***************************************************************
*
*  Copyright notice
*
*  (c) 2007	Benjamin Mack <www.xnos.org>
*  All rights reserved
*
*  Released under GNU/GPL (see license file in tslib/)
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
*
*  This copyright notice MUST APPEAR in all copies of this script
*
***************************************************************/


var SEO = {
	color: { yellow: "#ffff70", red: "#ff8040", green: "#c0ff70", white: "#ffffff" },
	registerFormFields: function() {
		$$('.seoTitleTag').each(
			function(el) {
				el.addEvent('keyup',    function() { this.checkTitleTag(el); }.bind(this) );
				el.addEvent('keypress', function() { this.checkTitleTag(el); }.bind(this) );
				this.checkTitleTag(el);
			}.bind(this)
		);

		$$('.seoKeywords').each(
			function(el) {
				el.addEvent('keyup',    function() { this.checkKeywords(el); }.bind(this) );
				el.addEvent('keypress', function() { this.checkKeywords(el); }.bind(this) );
				this.checkKeywords(el);
			}.bind(this)
		);

		$$('.seoDescription').each(
			function(el) {
				el.addEvent('keyup',    function() { this.checkDescription(el); }.bind(this) );
				el.addEvent('keypress', function() { this.checkDescription(el); }.bind(this) );
				this.checkDescription(el);
			}.bind(this)
		);

	},


	checkTitleTag: function(el) {
		var size = el.value.length;
		var color = "green";
		if (size > 70) { color = "red"; }
		if (size < 50) { color = "yellow"; }
		if (size == 0) { color = "white"; }
		el.style.backgroundColor = this.color[color];
	},


	checkKeywords: function(el) {
		var numKeywords = 0;
		if (el.value) {
			numKeywords = 1;
			var keywords = el.value.match(/,/gi);
			if (keywords) { numKeywords = keywords.length+1; }
		}
		var color = "green";
		if (numKeywords > 11) { color = "red"; }
		if (numKeywords < 3)  { color = "yellow"; }
		if (numKeywords < 1)  { color = "white"; }
		el.style.backgroundColor = this.color[color];
	},


	checkDescription: function(el) {
		var size = el.value.length;
		var color = "green";
		if (size > 150)  { color = "red"; }
		if (size < 115)  { color = "yellow"; }
		if (size ==  0)  { color = "white"; }
		el.style.backgroundColor = this.color[color];
	}
}

window.addEvent('domready', function() { SEO.registerFormFields(); } )
