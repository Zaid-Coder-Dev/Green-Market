/* LOGO PREVIEW */

const uploadArea =
document.getElementById("uploadArea");

const logoInput =
document.getElementById("logoInput");

const previewLogo =
document.getElementById("previewLogo");

uploadArea.addEventListener("click", () => {

   logoInput.click();

});

logoInput.addEventListener("change", function(){

   const file = this.files[0];

   if(file){

      const reader = new FileReader();

      reader.onload = function(e){

         previewLogo.src = e.target.result;

         previewLogo.style.display = "block";

      }

      reader.readAsDataURL(file);

   }

});

/* MARKET STATUS */

const toggleStatus =
document.getElementById("toggleStatus");

const statusText =
document.getElementById("statusText");

toggleStatus.addEventListener("click", () => {

   toggleStatus.classList.toggle("active");

   if(toggleStatus.classList.contains("active")){

      statusText.innerHTML = "Active";

   }

   else{

      statusText.innerHTML = "Non Active";

   }

});

/* PREVIEW */

const images =
document.querySelectorAll(".market-image img");

const previewImage =
document.getElementById("previewImage");

const prevBtn =
document.getElementById("prevBtn");

const nextBtn =
document.getElementById("nextBtn");

let current = 0;

images.forEach((img,index)=>{

   img.addEventListener("click", ()=>{

      previewImage.src = img.src;

      current = index;

   });

});

nextBtn.addEventListener("click", ()=>{

   current++;

   if(current >= images.length){
      current = 0;
   }

   previewImage.src = images[current].src;

});

prevBtn.addEventListener("click", ()=>{

   current--;

   if(current < 0){
      current = images.length - 1;
   }

   previewImage.src = images[current].src;

});

/* DELETE IMAGE */

const deleteBtns =
document.querySelectorAll(".delete-btn");

deleteBtns.forEach(btn=>{

   btn.addEventListener("click",(e)=>{

      e.target.closest(".market-image").remove();

   });

});