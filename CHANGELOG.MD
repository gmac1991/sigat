# SIGAT Changelog

## [1.3.4] - 2022-09-28

### Modificado
- [Busca]
Os resultados da busca rápida agora busca os dez últimos registros
- [Arquivo]
O arquivo agora traz os últimos 500 registros
- [Interação]
A tela de registro de interação foi ligeiramente diminuída para caber em telas menores

## [1.3.3] - 2022-09-28

### Corrigido
- [Interação]
Timestamps de alteracões de equipamentos no chamado não eram registrados
Finalização não esperada do chamado quando havia equipamentos em espera
- [Chamado]
Exibição de quantidade incorreta de anexos buscados do OTRS

### Adicionado
- [Chamado]
Novo botão para exibir o endereço do local do chamado
Alteração de prioridade é registrada no log de eventos e no histórico do chamado

### Removido
- [Painel]
Atualização automática dos painéis removida

## [1.2.3] - 2022-09-04

### Corrigido
- [Busca]
Erro ao usar a busca rápida

### Adicionado
- [Painel]
A última fila visitada agora é gravada em cookie para melhorar a navegação

## [1.2.2] - 2022-07-04

### Corrigido
- [Chamado]
Retornado valor 0 para ID do chamado após a importação.
- [Interação]
Registro duplicado de interações do tipo Entrega

## [1.2.1] - 2022-06-27

### Corrigido
- [Chamado] 
Remoção indevida de equipamentos

## [1.2.0] - 2022-06-24

### Corrigido
- [Chamado] 
Falha ao inserir novo equipamento
Remoção indevida de equipamentos
Rodapé sobrepondo a tabela de equipamentos

- [Interação]
Texto do registro de falha de entrega não é persistido
Renderização do laudo técnico com dados inconsistentes
Criação de interação inválida após erros no envio de arquivos

- [Triagem]
Usuário pode ter acesso à *view* de Triagem sem ter permissão

### Modificado
- [Chamado]
Sinalização de equipamentos em aberto na lista de equipamentos

### Adicionado
- [Triagem]
Comunicação com OTRS diretamente via API (devolução, importação e encerramento)
Painel com as informações em tempo real obtidos do OTRS
Descrição do ticket separado por artigo na tela de triagem
Download dos anexos diretamente da base de dados do OTRS

- [Chamado] 
Descrição do ticket separado por artigo na tela de chamado
Sinalização de prioridade no chamado
Download dos anexos diretamente da base de dados do OTRS

- [Painel] 
Coluna de percentual de conclusão no painel de chamados
Indicação visual do tempo decorrido desde a última interação no painel de chamados

- [Busca]
Pesquisa pelo número do chamado na busca rápida

### Removido
- [Triagem]
Removido armazenamento de tickets para triagem
Removido armazenamento do conteúdo dos anexos

