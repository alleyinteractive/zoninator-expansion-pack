// Zoninator Expansion Pack Scripts
'use strict';

jQuery( function( $ ) {

	if ( zoninator ) {
		zoninator.getAdvancedPostType = function() {
			return $('#zone_advanced_filter_post_type').length ? zoninator.$zoneAdvancedPostType.val() : 0;
		}
		zoninator.$zoneAdvancedPostType = $("#zone_advanced_filter_post_type");
		zoninator.$zoneAdvancedPostType.change( zoninator.updateLatest );

		$( '.zep-tax-filter' ).each( function( el ) {
			var $el = $( el );
			// zoninator.getAdvancedPostType = function() {
			// 	return $('#zone_advanced_filter_post_type').length ? zoninator.$zoneAdvancedPostType.val() : 0;
			// }
			// zoninator.$zoneAdvancedPostType = $("#zone_advanced_filter_post_type");
			// zoninator.$zoneAdvancedPostType.change( zoninator.updateLatest );
		} );

		zoninator.$zonePostSearch.bind( 'search.request', function( e,request ) {
			request.postType = zoninator.getAdvancedPostType();

			request.zep_date  = request.date;
			delete request.date;
		});

		zoninator.$zonePostSearch.bind( 'zoninator.ajax', function( e, action, data ) {
			if ( 'update_recent' == action ) {
				data.zep_date = data.date;
				delete data.date;
			}
		});

		// Eliminate the term cache because it doesn't take filters into account
		zoninator.$zonePostSearch.bind( 'loading.end', function( e ) {
			zoninator.autocompleteCache = [];
		});

		var autocomplete_item = _.template(
			'<a>' +
				'<span class="image"><%= thumbnail %></span>' +
				'<span class="details">' +
					'<span class="title"><%- title %></span>' +
					'<span class="type"><%- post_type %></span>' +
					'<span class="date"><%- date %></span>' +
					'<span class="status"><%- post_status %></span>' +
				'</span>' +
			'</a>'
		);

		/* Manipulate the results */
		var autocomplete = zoninator.$zonePostSearch.data( 'autocomplete' ) || zoninator.$zonePostSearch.data( 'ui-autocomplete' );
		if ( ! _.isUndefined( autocomplete ) ) {
			autocomplete._renderItem = function( ul, item ) {
				return $( '<li></li>' )
					.data( 'item.autocomplete', item )
					.append( autocomplete_item( item ) )
					.appendTo( ul )
					;
			}
		}
	}

});