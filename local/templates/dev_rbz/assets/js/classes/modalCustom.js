class ModalCustom
{
    constructor(props)
    {
        this.modal = document.querySelector(props.modal);
        this.container = document.querySelector(props.container);
        this.window = document.querySelector(props.window);
    }

    showLoading()
    {
        this.modal.classList.add('loading');
    }

    hideLoading()
    {
        this.modal.classList.remove('loading');
    }

    isContainerFilled()
    {
        return this.container.innerHTML.trim() !== '';
    }

    close()
    {
        this.modal.classList.remove('loading', 'active');
    }

    addEventOverlayClose()
    {
        this.modal.onclick = (e) => {
            let target = e.target;

            if (
                target.classList.contains('modal-custom')
                || target.classList.contains('modal-custom__wrap')
            ) {
                this.close();
            }
        };
    }

    addCloseButtonEvent()
    {
        let closeButtons = this.modal.querySelectorAll('[data-modal-custom-close]');

        if (closeButtons.length) {
            closeButtons.forEach(closeButton => {
                closeButton.onclick = () => {
                    this.close();
                }
            })
        }
    }

    open()
    {
        this.showLoading();

        this.addEventOverlayClose();

        this.addCloseButtonEvent();

        this.modal.classList.add('active');
    }

    showWindow()
    {
        this.window.classList.add('active');
    }

    addContent(html)
    {
        this.hideLoading();

        this.showWindow();

        this.appendWithScripts(this.container, 'beforeend', html);
    }

    appendWithScripts(target, position, html)
    {
        target.insertAdjacentHTML(position, html);

        let scripts = target.getElementsByTagName("script");

        while (scripts.length) {
            let script = scripts[0];
            script.parentNode.removeChild(script);

            let newScript = document.createElement("script");

            if (script.src) {
                newScript.src = script.src;
            } else if (script.textContent) {
                newScript.textContent = script.textContent;
            } else if (script.innerText) {
                newScript.innerText = script.innerText;
            }

            document.body.appendChild(newScript);
        }
    }
}