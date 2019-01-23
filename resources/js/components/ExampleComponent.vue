<template>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card card-default">
                    <div class="card-header">Brain.js example</div>

                    <div class="card-body">

                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                blackAndWhiteDate: [{input: { input: 0.03, g: 0.7, b: 0.5 }, output: { black: 1 }},
                        {input: { r: 0.16, g: 0.09, b: 0.2 }, output: { white: 1 }},
                        {input: { r: 0.5, g: 0.5, b: 1.0 }, output: { white: 1 }}
                    ]
            }
        },
        mounted() {
            console.log('Brain.js example mounted.');
            this.runBlackAndWhiteExample();
        },

        methods: {
            runBlackAndWhiteExample() {
                const net = new brain.NeuralNetwork();
                net.train(this.blackAndWhiteDate);
                const output = net.run({ r: 1, g: 0.4, b: 0 });  // { white: 0.99, black: 0.002 }
                console.log(output);
            }
        }
    }
</script>
