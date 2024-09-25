

/*$('.sub-menu ul').hide();

$(".sub-menu a").click(function () {
    $(this).parent(".sub-menu").children("ul").slideToggle("200");
    $(this).find("i.fa").toggleClass("fa-angle-up fa-angle-down");
});*/

document.addEventListener('DOMContentLoaded', () => {
    let items = document.querySelectorAll('.sub-menu a');
    let itemsSub = document.querySelectorAll('.sub-menu ul');



    if (items.length > 0) {
        items.forEach(item => {
            item.addEventListener('click', () => {
                // сокрытие активных
                itemsSub.forEach(itemSub => {
                    itemSub.classList.remove('active');
                });

                let parent = item.closest('.sub-menu');
                let child = parent.querySelector('ul');
                child.classList.toggle('active');
            });
        });
    }
});


