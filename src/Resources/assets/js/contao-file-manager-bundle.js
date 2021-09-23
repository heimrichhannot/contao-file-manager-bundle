import '@hundh/contao-utils-bundle';
import Swal from 'sweetalert2';

class FileManagerBundle {
    static init() {
        FileManagerBundle.initActions();
    }

    static initActions() {
        const fileManager = document.querySelector('.mod_huh_file_manager');

        if (fileManager.length < 1) {
            return;
        }

        const wrapper = fileManager.querySelector('.wrapper');

        utilsBundle.event.addDynamicEventListener('click', '.mod_huh_file_manager .actions a', (element, event) => {
            event.preventDefault();

            function doAction() {
                utilsBundle.ajax.get(element.getAttribute('href'), {}, {
                    onSuccess: (response) => {
                        if (element.classList.contains('delete')) {
                            let row = element.closest('tr');

                            row.parentNode.removeChild(row);
                        }

                        Swal.fire({
                            icon: 'success',
                            timer: 6000,
                            timerProgressBar: true,
                            showCloseButton: true,
                            showConfirmButton: false,
                            html: response.responseText
                        });
                    },
                    onError: (response) => {
                        Swal.fire({
                            icon: 'error',
                            timer: 6000,
                            timerProgressBar: true,
                            showCloseButton: true,
                            showConfirmButton: false,
                            html: response.responseText
                        });
                    }
                });
            }

            if (element.classList.contains('delete')) {
                Swal.fire({
                    html: element.getAttribute('data-delete-confirm-message'),
                    icon: 'question',
                    showCloseButton: true,
                    showConfirmButton: true,
                    showDenyButton: true,
                    confirmButtonText: wrapper.getAttribute('data-yes-label'),
                    denyButtonText: wrapper.getAttribute('data-no-label')
                }).then((result) => {
                    if (result.isConfirmed) {
                        doAction();
                    }
                });
            } else {
                doAction();
            }
        });
    }
}

export {FileManagerBundle};
