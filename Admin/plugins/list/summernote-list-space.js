/* Summernote Plugin: List Spacing Controller */
(function ($) {
  $.summernote = $.summernote || {};
  $.summernote.plugins = $.summernote.plugins || {};

  $.summernote.plugins.listSpace = function (context) {
    var ui = $.summernote.ui;

    function applySpacing(mode) {
      const map = {
        tight:  { lh: '1.20', mb: '2px'  },
        normal: { lh: '1.35', mb: '4px'  },
        loose:  { lh: '1.60', mb: '8px'  },
        high:   { lh: '1.35', mb: '200px' },
        reset:  { lh: '',     mb: ''     }
      };

      // restore selection lost by toolbar click
      context.invoke('editor.restoreRange');

      const rng = context.invoke('editor.getLastRange');
      if (!rng) return;

      const $editable = $(context.layoutInfo.editable);
      let $list = $(rng.sc).closest('ol,ul');
      if (!$list.length) {
        $list = $(rng.sc).parentsUntil($editable).filter('ol,ul').first();
      }
      if (!$list.length) return;

      // set inline styles on each <li> so it persists in HTML
      $list.find('li').each(function () {
        this.style.lineHeight   = map[mode].lh;
        this.style.marginBottom = map[mode].mb;
      });

      // mark as a command so Summernote updates its HTML + history
      context.invoke('editor.afterCommand');
    }

    context.memo('button.listSpace', function () {
      return ui.buttonGroup([
        ui.button({
          className: 'dropdown-toggle',
          contents: '<i class="note-icon-orderedlist"></i> Spacing <span class="caret"></span>',
          tooltip: 'Adjust list spacing',
          data: { toggle: 'dropdown' }
        }),
        ui.dropdown({
          className: 'dropdown-style',
          items: [
            '<a href="#" data-mode="tight">Tight</a>',
            '<a href="#" data-mode="normal">Normal</a>',
            '<a href="#" data-mode="loose">Loose</a>',
            '<a href="#" data-mode="high">High</a>',
            '<a href="#" data-mode="reset">Reset</a>'
          ],
          callback: function ($dd) {
            $dd.find('a').on('click', function (e) {
              e.preventDefault();
              applySpacing($(this).data('mode'));
            });
          }
        })
      ]).render();
    });
  };
})(jQuery);
