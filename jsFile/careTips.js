document.addEventListener("DOMContentLoaded", () => {

    const TIPS = {
        fire: {
            title: "🔥 Fire Emergency Tips",
            info: `
                • Stay low to avoid smoke.<br>
                • Do NOT use elevators during a fire.<br>
                • Call 999 immediately.<br>
                • If your clothes catch fire: STOP, DROP & ROLL.<br>
                • Keep a fire extinguisher and know how to use it.<br>
                • Check doors for heat before opening.
            `
        },

        pregnant: {
            title: "🤰 Pregnancy Care Tips",
            info: `
                • Drink plenty of clean water.<br>
                • Avoid lifting heavy objects.<br>
                • Take prenatal vitamins regularly.<br>
                • Visit your doctor for routine check-ups.<br>
                • Seek emergency care for bleeding or severe pain.<br>
                • Maintain a healthy diet and get enough rest.
            `
        },

        children: {
            title: "🧒 Child Emergency Tips",
            info: `
                • Keep all sharp objects out of reach.<br>
                • If choking: perform 5 back blows + 5 chest thrusts.<br>
                • For burns: rinse with cool water for 20 minutes.<br>
                • Do NOT give water to an unconscious child.<br>
                • Never leave children alone near water or fire.<br>
                • Call 999 if the child becomes unresponsive.
            `
        },

        special_child: {
            title: "🧑‍🦽 Special Child Care Tips",
            info: `
                • Maintain a calm and supportive environment.<br>
                • Learn and follow your child's emergency plan.<br>
                • Keep all required medications accessible.<br>
                • Ensure mobility aids (wheelchair, braces) are safe.<br>
                • Contact emergency services if there is breathing difficulty.<br>
                • Inform rescuers about your child's specific needs.
            `
        },

        senior: {
            title: "👵 Senior Citizen Care Tips",
            info: `
                • Keep floors dry and clutter-free to prevent falls.<br>
                • Ensure medications are taken correctly.<br>
                • Monitor for symptoms of stroke or heart attack.<br>
                • Maintain hydration to avoid dizziness.<br>
                • Use assistive devices (sticks/walkers) if needed.<br>
                • Call 999 if sudden weakness or chest pain occurs.
            `
        },

        firstaid: {
            title: "⛑️ First Aid Basics",
            info: `
                • Check if the area is safe before helping.<br>
                • For bleeding: apply pressure with a clean cloth.<br>
                • For fractures: immobilize the area and avoid movement.<br>
                • For unconscious victims: check breathing immediately.<br>
                • Do NOT give food/water to someone in shock.<br>
                • Always call 999 when unsure.
            `
        },

        mental_health: {
            title: "🧠 Mental Health Support",
            info: `
                • Take slow, deep breaths to reduce anxiety.<br>
                • Avoid being alone during panic episodes.<br>
                • Talk to a trusted friend or family member.<br>
                • If suicidal thoughts appear, call emergency services.<br>
                • Maintain regular sleep and avoid excessive stress.<br>
                • Seek professional counseling if episodes repeat.
            `
        },

        disaster: {
            title: "🌊 Flood & Cyclone Safety Tips",
            info: `
                • Move to higher ground immediately.<br>
                • Avoid walking or driving through floodwaters.<br>
                • Keep emergency items: water, torch, first aid.<br>
                • Disconnect electrical appliances before evacuation.<br>
                • Stay updated with official weather alerts.<br>
                • Call 999 if someone is trapped or drowning.
            `
        }
    };

    // Handle card clicks
    const cards = document.querySelectorAll(".tips-card");
    const modal = document.getElementById("tips-modal");
    const modalBox = document.getElementById("tips-modal-box");

    cards.forEach(card => {
        card.addEventListener("click", () => {
            let topic = card.dataset.topic;
            let data = TIPS[topic];

            document.getElementById("tips-title").innerHTML = data.title;
            document.getElementById("tips-info").innerHTML = data.info;

            // open modal
            modal.checked = true;
            modalBox.classList.remove("hidden");
            modalBox.classList.add("flex");
        });
    });

    // Close modal on checkbox uncheck
    modal.addEventListener("change", () => {
        if (!modal.checked) {
            modalBox.classList.add("hidden");
            modalBox.classList.remove("flex");
        }
    });
});
