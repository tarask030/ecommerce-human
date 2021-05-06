const cookieContainer = document.querySelector(".cookie-container");
const cookieButton = document.querySelector(".cookie-btn");

cookieButton.addEventListener("click", () => {
  cookieContainer.classList.remove("active");
  localStorage.setItem("cookieBannerDisplayed", "true");
});

setTimeout(() => {
  if (!localStorage.getItem("cookieBannerDisplayed")) {
    cookieContainer.classList.add("active");
  }
}, 2000);

function myFunction() {
	window.open("https://solutions4ad.com/partner/scripts/l2d1sz5?AccountId=2361fbf0&TotalCost=0&OrderID= ' .$phone. ' &ActionCode=Humanit_CPS&CampaignID=e95d00cb");
       }
