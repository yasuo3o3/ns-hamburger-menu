document.addEventListener('DOMContentLoaded', function() {
    // Initialize WordPress color picker for custom color inputs
    if (typeof jQuery !== 'undefined' && jQuery.fn.wpColorPicker) {
        jQuery('.nshm-color').wpColorPicker();
    }

    // Handle color preset radio button changes
    const presetRadios = document.querySelectorAll('input[name="ns_hamburger_options[color_preset]"]');
    const customSettings = document.querySelector('.nshm-custom-settings');

    if (presetRadios.length && customSettings) {
        // Function to toggle custom settings visibility
        function toggleCustomSettings() {
            const checkedRadio = document.querySelector('input[name="ns_hamburger_options[color_preset]"]:checked');
            if (checkedRadio && checkedRadio.value === 'custom') {
                customSettings.style.display = 'block';
            } else {
                customSettings.style.display = 'none';
            }
        }

        // Set initial state
        toggleCustomSettings();

        // Add event listeners to all preset radio buttons
        presetRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                toggleCustomSettings();
                
                // If a preset (non-custom) is selected, update color inputs
                if (this.value !== 'custom') {
                    const presets = {
                        blue:   {start:'#0ea5e9', end:'#60a5fa'},
                        green:  {start:'#22c55e', end:'#86efac'},
                        red:    {start:'#ef4444', end:'#f87171'},
                        orange: {start:'#f59e0b', end:'#fdba74'},
                        black:  {start:'#0b0b0b', end:'#575757'}
                    };

                    const preset = presets[this.value];
                    if (preset) {
                        const startInput = document.querySelector('input[name="ns_hamburger_options[color_start]"]');
                        const endInput = document.querySelector('input[name="ns_hamburger_options[color_end]"]');

                        if (startInput && endInput) {
                            startInput.value = preset.start;
                            endInput.value = preset.end;

                            // Trigger change event for WordPress color picker
                            if (typeof jQuery !== 'undefined' && jQuery.fn.wpColorPicker) {
                                jQuery(startInput).wpColorPicker('color', preset.start);
                                jQuery(endInput).wpColorPicker('color', preset.end);
                            }
                        }
                    }
                }
            });
        });
    }
});
