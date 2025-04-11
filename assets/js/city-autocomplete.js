jQuery(document).ready(function($) {
    // Attends que le formulaire Elementor soit prêt
    $(document).on('elementor/popup/show', function() {
        initializeAutocomplete();
    });
    
    const country = monObjetJs.country; 

    initializeAutocomplete(country);
    
    function initializeAutocomplete(country) {
        $('.city-autocomplete').each(function() {
            if (!this.autocomplete) {
                const autocomplete = new google.maps.places.Autocomplete(this, {
                    types: ['(cities)'], // Limite aux villes uniquement
                    componentRestrictions: { country: country },
                    fields: ['name'] // Récupère uniquement le nom
                });
                
                // Stocke l'instance pour éviter les doublons
                this.autocomplete = autocomplete;
                
                // Gestion de la sélection
                autocomplete.addListener('place_changed', function() {
                    const place = autocomplete.getPlace();
                    //console.log(place)
                    if (place.name) {
                        this.value = place.name;
                    }
                });
            }
        });
    }
}); 