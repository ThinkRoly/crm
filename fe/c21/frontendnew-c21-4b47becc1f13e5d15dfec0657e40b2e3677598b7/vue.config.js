module.exports = {
    devServer: {
      proxy: {
        '/api': {
          target: 'http://101.201.60.94',
          changeOrigin: true,
          ws: true,
        }
      }
    }
  }