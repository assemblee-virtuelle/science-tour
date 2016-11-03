myFile.addEventListener('change', function() {
	if (myFile.files[0].size > 2097152) {
		alert('La taille de l\'image doit être inférieure à 2 Mo.');
		myFile.value = "";
	} else {
		var img = new Image();
		img.onload = function() {
			if (this.width > 4000 || this.height > 4000) {
				alert('La résolution de l\'image doit être inférieure à 4000 x 4000 pixels.');
				myFile.value = "";
			}
		};
		img.onerror = function() {
			alert("Le fichier doit être une image");
			myFile.value = "";
		};
		img.src = window.URL.createObjectURL(myFile.files[0]);
	}
});