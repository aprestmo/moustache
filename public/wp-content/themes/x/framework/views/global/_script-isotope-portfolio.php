<?php

// =============================================================================
// VIEWS/GLOBAL/_SCRIPT-ISOTOPE-PORTFOLIO.PHP
// -----------------------------------------------------------------------------
// Isotope script call for portfolio output.
// =============================================================================

wp_enqueue_script( 'x-stack' );

$stack  = x_get_stack();
$is_rtl = is_rtl();

?>

<script>

  document.addEventListener('DOMContentLoaded', () => {
    var $ = window.jQuery;
    if (!$ || !$.xIsotope) {
      console.warn('Missing jQuery or Isotope')
      return;
    }

    <?php if ( $is_rtl ) : ?>

      $.xIsotope.prototype._positionAbs = function( x, y ) {
        return { right: x, top: y };
      };

    <?php endif; ?>

    var $container   = $('#x-iso-container');
    var $optionSets  = $('.option-set');
    var $optionLinks = $optionSets.find('a');

    $container.before('<span id="x-isotope-loading" class="x-loading"><span>');

    function loadIsotope() {
      $container.xIsotope({
        itemSelector   : '.x-iso-container > *',
        resizable      : true,
        filter         : '*',
        <?php if ( $is_rtl ) : ?>
          transformsEnabled : false,
        <?php endif; ?>
        containerStyle : {
          overflow : 'hidden',
          position : 'relative'
        }
      });
      $('#x-isotope-loading').stop(true,true).fadeOut(300);
      $('#x-iso-container > *').each(function(i) {
        $(this).delay(i * 150).animate({'opacity' : 1},500,'xEaseIsotope');
      });
    }

    if (document.readyState === 'complete') {
      loadIsotope()
    } else {
      $(window).on('load', loadIsotope);
    }

    $(window).xsmartresize(function() {
      $container.xIsotope({  });
    });

    $optionLinks.on( 'click', function() {
      var $this = $(this);
      if ( $this.hasClass('selected') ) {
        return false;
      }
      var $optionSet = $this.parents('.option-set');
      $optionSet.find('.selected').removeClass('selected');
      $this.addClass('selected');
      <?php if ( $stack == 'ethos' ) : ?>
        $('.x-portfolio-filter-label').text($this.text());
      <?php endif; ?>
      var options = {},
          key     = $optionSet.attr('data-option-key'),
          value   = $this.attr('data-option-value');
      value        = value === 'false' ? false : value;
      options[key] = value;
      if ( key === 'layoutMode' && typeof changeLayoutMode === 'function' ) {
        changeLayoutMode( $this, options );
      } else {
        $container.xIsotope( options );
      }
      return false;
    });

    $('.x-portfolio-filters').on('click', function() {
      $(this).parent().find('ul').slideToggle(600, 'xEaseIsotope');
    });

  });

</script>
