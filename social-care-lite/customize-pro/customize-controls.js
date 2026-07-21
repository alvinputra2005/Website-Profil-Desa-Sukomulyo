( function( api ) {
	// Extends our custom "social-care-lite" section.
	api.sectionConstructor['social-care-lite'] = api.Section.extend( {
		// No events for this type of section.
		attachEvents: function () {},
		// Always make the section active.
		isContextuallyActive: function () {
			return true;
		}
	} );
} )( wp.customize );