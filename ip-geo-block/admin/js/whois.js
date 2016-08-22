/*jslint white: true */
/*!
 * Project: WordPress IP Geo Block
 * Copyright (c) 2016 tokkonopapa (tokkonopapa@yahoo.com)
 * This software is released under the MIT License.
 */
(function ($, window, document) {
	'use strict';
	$(window).on('ip-geo-block-whois', function () {
		/**
		 * APIs that doesn't support CORS.
		 * These are accessed through https://developer.yahoo.com/yql/
		 */
		var yql = 'https://query.yahooapis.com/v1/public/yql?q=select * from xml where url="%URL%"&format=json&jsonCompat=new',
		    url = '//rest.db.ripe.net/search%3fflags=no-filtering%26flags=resource%26query-string=',
		    ip = $('#ip_geo_block_settings_ip_address').val().replace(/[^\d\.:]/, '');

		if (ip) {
			$.ajax({
				url: yql.replace(/%URL%/, window.location.protocol + url + ip),
				method: 'GET',
				dataType: 'json'
			})

			.done(function (data, textStatus, jqXHR) {
				var i, j, objs, attr, $whois = $('#ip-geo-block-whois');
				objs = data.query.results['whois-resources'].objects.object;

				for (i = 0; i < objs.length; i++) {
					attr = objs[i].attributes.attribute;

					for (j = 0; j < attr.length; j++) {
						console.log(JSON.stringify(attr[j]));
						console.log('%s: %s', attr[j].name, attr[j].value);
					}
				}
			})

			.fail(function (jqXHR, textStatus, errorThrown) {
			});
		}
	});
}(jQuery, window, document));