/**
 * Antigravity Particles Animation
 * Simulates floating colorful particles similar to Google Antigravity
 */

(function () {
    // Canvas and context will be initialized in init()
    let canvas, ctx;
    let width, height;
    let particles = [];

    // Configuration
    const particleCount = 150; // Number of particles
    const colors = [
        '#4285F4', // Google Blue
        '#DB4437', // Google Red
        '#F4B400', // Google Yellow
        '#0F9D58', // Google Green
        '#AB47BC', // Purple
        '#00ACC1', // Cyan
        '#FF7043', // Orange
        '#9E9D24'  // Lime
    ];

    // Resize handling
    function resize() {
        width = canvas.width = window.innerWidth;
        height = canvas.height = window.innerHeight;
    }

    // Particle class
    class Particle {
        constructor() {
            this.init();
        }

        init() {
            this.x = Math.random() * width;
            this.y = Math.random() * height;

            // Random direction and speed
            this.vx = (Math.random() - 0.5) * 1;
            this.vy = (Math.random() - 0.5) * 1;

            this.size = Math.random() * 3 + 1; // Size between 1 and 4
            this.color = colors[Math.floor(Math.random() * colors.length)];

            // For "dash" look
            this.isDash = Math.random() > 0.5;
            this.angle = Math.random() * Math.PI * 2;
            this.dashLength = this.size * 3;
        }

        update() {
            this.x += this.vx;
            this.y += this.vy;

            // Bounce off edges (or wrap around - let's wrap for better flow)
            if (this.x < -10) this.x = width + 10;
            if (this.x > width + 10) this.x = -10;
            if (this.y < -10) this.y = height + 10;
            if (this.y > height + 10) this.y = -10;

            // Slowly rotate dashes
            if (this.isDash) {
                this.angle += 0.01;
            }
        }

        draw() {
            ctx.beginPath();
            if (this.isDash) {
                // Draw as a small dash/capsule
                const x2 = this.x + Math.cos(this.angle) * this.dashLength;
                const y2 = this.y + Math.sin(this.angle) * this.dashLength;

                ctx.lineWidth = this.size;
                ctx.strokeStyle = this.color;
                ctx.lineCap = 'round';
                ctx.moveTo(this.x, this.y);
                ctx.lineTo(x2, y2);
                ctx.stroke();
            } else {
                // Draw as a circle
                ctx.fillStyle = this.color;
                ctx.arc(this.x, this.y, this.size, 0, Math.PI * 2);
                ctx.fill();
            }
        }
    }

    function init() {
        canvas = document.createElement('canvas');
        canvas.id = 'particles-canvas';
        canvas.style.position = 'fixed';
        canvas.style.top = '0';
        canvas.style.left = '0';
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        canvas.style.pointerEvents = 'none'; // Allow clicking through
        canvas.style.zIndex = '-1'; // Behind everything
        document.body.prepend(canvas);

        ctx = canvas.getContext('2d');

        resize();
        for (let i = 0; i < particleCount; i++) {
            particles.push(new Particle());
        }
        animate();
    }

    function animate() {
        ctx.clearRect(0, 0, width, height);

        particles.forEach(p => {
            p.update();
            p.draw();
        });

        requestAnimationFrame(animate);
    }

    window.addEventListener('resize', resize);

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
