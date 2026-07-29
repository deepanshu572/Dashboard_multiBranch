function encrypt(data) {
  return btoa(data);
}

function sendNotification() {
  const titlePush = document.getElementById('titlePush').value;
  const message = document.getElementById('message').value;
  const imageFile = document.getElementById('imageUpload').files[0];

  const formData = new FormData();
  formData.append('dbUserNm', encrypt('u373855149_bachatfresh2'));
  formData.append('dbPass', encrypt('Bachatfresh2@123'));
  formData.append('dbName', encrypt('u373855149_bachatfresh2'));
  formData.append('projectId', 'bachat-fresh-kirana-466e6');
  formData.append('pvKeyUrl', 'https://indiantechsolution.com/pvkey/bachatfresh2/pvkey.json');
  formData.append('message', message);
  formData.append('titlePush', titlePush);

  if (imageFile) {
    formData.append('image', imageFile);
  }

  // Show loader
  Swal.fire({
    title: 'Sending...',
    text: 'Please wait while we send your notification.',
    allowOutsideClick: false,
    didOpen: () => {
      Swal.showLoading();
    }
  });

  fetch('https://indiantechsolution.com/push_notification/send.php', {
    method: 'POST',
    body: formData
  })
    .then(response => {
      if (!response.ok) {
        throw new Error('Network response was not ok');
      }
      return response.json();
    })
    .then(data => {
      Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: 'Notification sent successfully.'
      });

      // Clear input fields
      document.getElementById('titlePush').value = '';
      document.getElementById('message').value = '';
      document.getElementById('imageUpload').value = '';
    })
    .catch((error) => {
      Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: 'Something went wrong while sending notification!'
      });
      console.error('Error:', error);
    });
}



