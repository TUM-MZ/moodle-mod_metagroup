import nodeResolve from '@rollup/plugin-node-resolve'
import commonjs from '@rollup/plugin-commonjs'
// import livereload from 'rollup-plugin-livereload'
import { uglify } from 'rollup-plugin-uglify'

export default ({
  input: 'es6/metalink.js',
  output: {
    file: 'build/metalink.min.js',
    format: 'amd'
  },
  plugins: [
    nodeResolve(),
    commonjs(),
    uglify()
    // livereload()
  ]
})
