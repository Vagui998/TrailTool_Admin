$(document).ready(function () {
    if (typeof $.AdminLTE !== 'undefined') {
        // Manually initialize layout and other components
        $.AdminLTE.layout.activate();
        $.AdminLTE.pushMenu.activate('[data-widget="pushmenu"]');
        $.AdminLTE.tree('.nav-sidebar');
    $.ajax({
        url: '../pages/shared/fetch_sidebar_data.php',
        method: 'GET',
        dataType: 'json',
        success: function (data) {
            console.log("Received JSON:", data);

            var $sidebarMenu = $('.nav-sidebar');
            $sidebarMenu.empty(); // Clear any existing items

            function buildMenuItems(items) {
                var $ul = $('<ul />', { class: 'nav nav-treeview' });
                $.each(items, function (index, item) {
                    var iconClass = item.icon_class ? item.icon_class : 'far fa-circle'; // Use a default icon if not defined
                    var $menuItem = $('<li class="nav-item"></li>');
                    var $link = $('<a></a>', {
                        href: item.link ? item.link : '#',
                        class: 'nav-link',
                        html: '<i class="nav-icon ' + iconClass + '"></i><p>' + item.name + (item.children.length > 0 ? '<i class="right fas fa-angle-left"></i>' : '') + '</p>'
                    });

                    $menuItem.append($link);

                    if (item.children.length > 0) {
                        $menuItem.addClass('has-treeview');
                        $menuItem.append(buildMenuItems(item.children));
                    }

                    $ul.append($menuItem);
                });
                return $ul;
            }

            $sidebarMenu.append(buildMenuItems(data));

            // Explicitly call layout and treeview fixes
            if (typeof $.AdminLTE !== 'undefined') {
                $.AdminLTE.layout.fix();
                $.AdminLTE.tree();
            } else {
                console.error("AdminLTE is not loaded correctly.");
            }
        },
        error: function (xhr, status, error) {
            console.error('Failed to load sidebar items:', error);
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
        }
    });
    } else {
        console.error("AdminLTE is not loaded correctly.");
    }
});
