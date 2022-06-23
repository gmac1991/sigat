# SIGAT Changelog

## [1.2.0] - 24/06/2022

### Corrigido
- [Chamado] 
-- Falha ao inserir novo equipamento
-- Rodapé sobrepondo a tabela de equipamentos
- [Interação]
-- Texto do registro de falha de entrega não é persistido
-- Renderização do laudo técnico com dados inconsistentes
-- Criação de interação inválida após erros no envio de arquivos
- [Triagem]
-- Usuário pode ter acesso à **view** de Triagem sem ter permissão

### Modificado
- [Interação]
-- Inserido ID da interação no laudo técnico
-- Inserido versão do SIGAT no rodapé de todos os termos

### Adicionado
- [Triagem]
-- Comunicação com OTRS diretamente via API (devolução, importação e encerramento)
-- Painel com as informações em tempo real obtidos do OTRS
-- Descrição do ticket separado por artigo na tela de triagem

- [Chamado] 
-- Descrição do ticket separado por artigo na tela de chamado
-- Sinalização de prioridade no chamado

- [Painel] 
-- Coluna de percentual de conclusão no painel de chamados
-- Indicação visual do tempo decorrido desde a última interação no painel de chamados

- [Busca]
-- Pesquisa pelo número do chamado na busca rápida



