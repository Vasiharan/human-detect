<!DOCTYPE html>
<html>
<head>
    <title>brain.js</title>
    <script src="https://cdn.rawgit.com/BrainJS/brain.js/master/browser.js"></script>
    <script>
        function DrawableCanvas(el) {
            const px = 10
            const ctx = el.getContext('2d')
            let x = []
            let y = []
            let moves = []
            let isPainting = false

            const clear = () => ctx.clearRect(0, 0, ctx.canvas.width, ctx.canvas.height)

            const addPoint = (_x, _y, isMoving) => {
                x.push(_x)
                y.push(_y)
                moves.push(isMoving)
            }

            const redraw = () => {
                clear()

                ctx.strokeStyle = 'red'
                ctx.lineJoin = 'round'
                ctx.lineWidth = px

                for (let i = 0; i < moves.length; i++) {
                    ctx.beginPath()
                    if (moves[i] && i) {
                        ctx.moveTo(x[i - 1], y[i - 1])
                    } else {
                        ctx.moveTo(x[i] - 1, y[i])
                    }
                    ctx.lineTo(x[i], y[i])
                    ctx.closePath()
                    ctx.stroke()
                }
            }

            const drawLine = (x1, y1, x2, y2, color = 'lightgray') => {
                ctx.beginPath()
                ctx.strokeStyle = color
                ctx.lineJoin = 'miter'
                ctx.lineWidth = 1
                ctx.moveTo(x1, y1)
                ctx.lineTo(x2, y2)
                ctx.stroke()
            }

            const grid = () => {
                const w = el.clientWidth
                const h = el.clientHeight
                const p = el.clientWidth / px
                const xStep = w / p
                const yStep = h / p

                for(let x = 0; x < w; x += xStep) {
                    drawLine(x, 0, x, h)
                }
                for(let y = 0; y < h; y += yStep) {
                    drawLine(0, y, w, y)
                }
            }

            const cell = (x, y, w, h) => {
                ctx.fillStyle = 'blue'
                ctx.strokeStyle = 'blue'
                ctx.lineJoin = 'miter'
                ctx.lineWidth = 1
                ctx.rect(x, y, w, h)
                ctx.fill()
            }


            this.reset = () => {
                isPainting = false
                x = []
                y = []
                moves = []
                clear()
            }

            this.getVector = (debug = false) => {
                const w = el.clientWidth
                const h = el.clientHeight
                const p = el.clientWidth / px
                const xStep = w / p
                const yStep = h / p
                const vector = []
                for(let x = 0; x < w; x += xStep) {
                    for(let y = 0; y < h; y += yStep) {
                        const data = ctx.getImageData(x, y, xStep, yStep)

                        let nonEmptyPixelsCount = 0
                        for(let i = 0; i < data.data.length; i += 4) {
                            const isEmpty = data.data[i] === 0
                            if (!isEmpty) {
                                nonEmptyPixelsCount += 1
                            }
                        }

                        if (nonEmptyPixelsCount > 1 && debug) {
                            cell(x, y, xStep, yStep)
                        }

                        vector.push(nonEmptyPixelsCount > 1 ? 1 : 0)
                    }
                }

                if (debug) {
                    grid()
                }
                return vector
            }

            el.addEventListener('mousedown', event => {
                const bounds = event.target.getBoundingClientRect()
                const x = event.clientX - bounds.left
                const y = event.clientY - bounds.top
                isPainting = true
                addPoint(x, y, false)
                redraw()
            })

            el.addEventListener('mousemove', event => {
                const bounds = event.target.getBoundingClientRect()
                const x = event.clientX - bounds.left
                const y = event.clientY - bounds.top
                if (isPainting) {
                    addPoint(x, y, true)
                    redraw()
                }
            })

            el.addEventListener('mouseup', () => {
                isPainting = false
            })

            el.addEventListener('mouseleave', () => {
                isPainting = false
            })
        }
    </script>
</head>
<body>
    <div class="app" id="app">

        <example-component></example-component>
    
    </div>
    <table align="center" cellspacing="0" cellpadding="1" border="0">
        <caption>
            <h3>Step 1: Prepare data</h3>
            <p>Draw 3 positive and 3 negative images</p>
        </caption>
        <tr>
            <th>Positive</th>
            <td>
                <canvas id="p1" width="200" height="200" style="border: 1px solid black; cursor: default; display: block;"></canvas>
            </td>
            <td>
                <canvas id="p2" width="200" height="200" style="border: 1px solid black; cursor: default; display: block;"></canvas>
            </td>
            <td>
                <canvas id="p3" width="200" height="200" style="border: 1px solid black; cursor: default; display: block;"></canvas>
            </td>
        </tr>
        <tr>
            <th>Negative</th>
            <td>
                <canvas id="n1" width="200" height="200" style="border: 1px solid black; cursor: default; display: block;"></canvas>
            </td>
            <td>
                <canvas id="n2" width="200" height="200" style="border: 1px solid black; cursor: default; display: block;"></canvas>
            </td>
            <td>
                <canvas id="n3" width="200" height="200" style="border: 1px solid black; cursor: default; display: block;"></canvas>
            </td>
        </tr>
    </table>

    <table width="100%" align="center">
        <caption>
            <h3>Step 2: Train Model</h3>
            <button id="train">Train</button>
            <label><input type="checkbox" id="dbg"/> debug</label>
        </caption>
        <tr>
            <td align="center">
                <table>
                    <tbody id="res" style="display: none">
                        <tr>
                            <th>Error</th>
                            <td id="err"></td>
                        </tr>
                        <tr>
                            <th>Iterations</th>
                            <td id="iterations"></td>
                        </tr>
                    </tbody>
                </table>
            </td>
        </tr>
    </table>

    <table align="center">
        <caption>
            <h3>Step 3: Evaluate Model</h3>
            <button id="guess">Guess</button>
        </caption>
        <tr>
            <td align="center">
                <canvas id="g" width="200" height="200" style="border: 1px solid black; cursor: default; display: block;"></canvas>
            </td>
        </tr>
    </table>

    <script>
        const pc1 = new DrawableCanvas(document.getElementById('p1'))
        const pc2 = new DrawableCanvas(document.getElementById('p2'))
        const pc3 = new DrawableCanvas(document.getElementById('p3'))
        const nc1 = new DrawableCanvas(document.getElementById('n1'))
        const nc2 = new DrawableCanvas(document.getElementById('n2'))
        const nc3 = new DrawableCanvas(document.getElementById('n3'))
        const gc = new DrawableCanvas(document.getElementById('g'))

        const net = new brain.NeuralNetwork()

        train.addEventListener('click', () => {
            const data = []

            data.push({ input: pc1.getVector(dbg.checked), output: {positive: 1} })
            data.push({ input: pc2.getVector(dbg.checked), output: {positive: 1} })
            data.push({ input: pc3.getVector(dbg.checked), output: {positive: 1} })
            data.push({ input: nc1.getVector(dbg.checked), output: {negative: 1} })
            data.push({ input: nc2.getVector(dbg.checked), output: {negative: 1} })
            data.push({ input: nc3.getVector(dbg.checked), output: {negative: 1} })

            const result = net.train(data, {log: true})
            err.innerHTML = result.error
            iterations.innerHTML = result.iterations
            res.removeAttribute('style')
        })

        guess.addEventListener('click', () => {
            const result = brain.likely(gc.getVector(), net)
            alert(result)
            gc.reset()
        })
    </script>
    <script src="/js/app.js"></script>
</body>
</html>
