function buyOneClickHandler()
{
    let btnBuyOneClick = document.querySelector('.product-item-btn-buy-one-click');

    if (btnBuyOneClick) {
        btnBuyOneClick.addEventListener('click', (e) => {
            e.preventDefault();

            btnBuyOneClick.disabled = true;

            let modal = new ModalCustom({
                'modal': '.modal-buy-one-click',
                'container': '.modal-buy-one-click__container',
                'window': '.modal-buy-one-click__window'
            });

            modal.open();

            let productId = btnBuyOneClick.getAttribute('data-product-id'),
                formData = new FormData();

            if (productId) {
                formData.append("PRODUCT_ID", productId);
            }

            if (modal.isContainerFilled()) {
                modal.hideLoading();

                btnBuyOneClick.disabled = false;
            } else {
                fetch('/ajax/buy-one-click.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text())
                .then(html => {
                    modal.addContent(html);
                    btnBuyOneClick.disabled = false;
                })
                .catch(error => {
                    alert('Какая-то непредвиденная ошибка, обновите страницу');
                    modal.close();
                    btnBuyOneClick.disabled = false;
                });
            }
        });
    }
}



document.addEventListener('DOMContentLoaded', () => {
    buyOneClickHandler();
});