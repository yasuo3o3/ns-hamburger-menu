jQuery(function($){
  $('.ns-color').wpColorPicker();

  const $scheme = $('#ns_scheme');
  if ($scheme.length) {
    const presets = {
      blue:   {start:'#0ea5e9', end:'#60a5fa'},
      green:  {start:'#22c55e', end:'#86efac'},
      red:    {start:'#ef4444', end:'#f87171'},
      orange: {start:'#f59e0b', end:'#fdba74'},
      black:  {start:'#0b0b0b', end:'#575757'}
    };
    const name = 'ns_hamburger_options';
    const $start = $('input[name="'+name+'[color_start]"]');
    const $end   = $('input[name="'+name+'[color_end]"]');

    $scheme.on('change', function(){
      const v = $(this).val();
      if (presets[v]) {
        $start.val(presets[v].start).trigger('change');
        $end.val(presets[v].end).trigger('change');
      }
    });
  }
});
