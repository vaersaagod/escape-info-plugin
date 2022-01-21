!function(){var e=document.getElementById("adspace-popup"),t=e?e.firstElementChild:null;if(e&&t){for(var a=JSON.parse(e.dataset.ads)||[],l=null,r=0;r<a.length;r+=1){l=a[r];break}if(l){var n=t.querySelector("iframe");n&&(n.addEventListener("load",(function(){console.log("I loaded!")})),n.setAttribute("src",l.url))}}}();
//# sourceMappingURL=adspace-popup.js.map
