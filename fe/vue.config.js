module.exports = {
    devServer: {
      proxy: {
        '/api': {
          target: 'http://47.109.157.246',
          changeOrigin: true,
          ws: true,
        }
      }
    }
  }